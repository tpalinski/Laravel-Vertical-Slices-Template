<?php

declare(strict_types=1);

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Persistence\Model\UserCredentials;
use Modules\Auth\Domain\Repository\User\UserCredentialsRepository;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $repo = $this->app->make(UserCredentialsRepository::class);

        $user = new UserCredentials([
            'userId' => 'user-123',
            'login' => 'john',
        ]);
        $user->password = Hash::make('secret');

        $repo->addUser($user);
    }

    public function test_full_authorization_code_flow_until_authorize(): void
    {
        /* =========================================================
         * STEP 1: LOGIN → get auth ticket
         * ========================================================= */

        $loginResponse = $this->postJson('/auth/login', [
            'login' => 'john',
            'password' => 'secret',
            'clientId' => 'nxsfr',
        ]);

        $loginResponse->assertStatus(201);

        $ticket = $loginResponse->json('authTicket');

        $this->assertNotEmpty($ticket);

        /* =========================================================
         * STEP 2: AUTHORIZE → expect redirect with code
         * ========================================================= */

        $authorizeResponse = $this->get('/auth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'nxsfr',
            'redirect_uri' => 'http://127.0.0.1',
            'scope' => 'module:read',
            'state' => 'xyz',
            'authTicket' => $ticket,
        ]));

        $authorizeResponse->assertStatus(302);

        $location = $authorizeResponse->headers->get('Location');
        $this->assertNotNull($location);

        // Extract authorization code from redirect
        parse_str(parse_url($location, PHP_URL_QUERY), $queryParams);

        $this->assertArrayHasKey('code', $queryParams);
        $this->assertEquals('xyz', $queryParams['state']);

        $authCode = $queryParams['code'];

        /* =========================================================
         * STEP 3: TOKEN → exchange code for access token
         * ========================================================= */

        $tokenResponse = $this->post('/auth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'redirect_uri' => 'http://127.0.0.1',
            'code' => $authCode,
        ]);

        $tokenResponse->assertStatus(200);

        $data = json_decode($tokenResponse->getContent(), true);

        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertEquals('Bearer', $data['token_type']);
    }

    public function test_login_with_invalid_password_returns_401(): void
    {
        $response = $this->postJson('/auth/login', [
            'login' => 'john',
            'password' => 'wrong-password',
            'clientId' => 'nxsfr',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_with_nonexistent_user_returns_404(): void
    {
        $response = $this->postJson('/auth/login', [
            'login' => 'does-not-exist',
            'password' => 'secret',
            'clientId' => 'nxsfr',
        ]);

        $response->assertStatus(404);
    }

    public function test_authorize_with_invalid_auth_ticket_returns_401(): void
    {
        $response = $this->get('/auth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'nxsfr',
            'redirect_uri' => 'http://127.0.0.1',
            'scope' => 'module:read',
            'state' => 'xyz',
            'authTicket' => 'invalid-ticket',
        ]));

        $response->assertStatus(401);
    }

    public function test_authorize_with_invalid_client_returns_401(): void
    {
        // First get a valid ticket
        $loginResponse = $this->postJson('/auth/login', [
            'login' => 'john',
            'password' => 'secret',
            'clientId' => 'nxsfr',
        ]);

        $ticket = $loginResponse->json('authTicket');

        $response = $this->get('/auth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'invalid-client',
            'redirect_uri' => 'http://127.0.0.1',
            'scope' => 'module:read',
            'state' => 'xyz',
            'authTicket' => $ticket,
        ]));

        $response->assertStatus(401);
    }

    public function test_token_with_invalid_code_fails(): void
    {
        $response = $this->post('/auth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'redirect_uri' => 'http://127.0.0.1',
            'code' => 'invalid-code',
        ]);

        $response->assertStatus(400);
    }

    public function test_token_with_invalid_client_credentials_fails(): void
    {
        $loginResponse = $this->postJson('/auth/login', [
            'login' => 'john',
            'password' => 'secret',
            'clientId' => 'nxsfr',
        ]);

        $ticket = $loginResponse->json('authTicket');

        $authorizeResponse = $this->get('/auth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => 'nxsfr',
            'redirect_uri' => 'http://127.0.0.1',
            'scope' => 'module:read',
            'state' => 'xyz',
            'authTicket' => $ticket,
        ]));

        $location = $authorizeResponse->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $queryParams);

        $authCode = $queryParams['code'];

        // Use WRONG secret
        $response = $this->post('/auth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'pnl',
            'client_secret' => 'wrong-secret',
            'redirect_uri' => 'http://127.0.0.1',
            'code' => $authCode,
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [400, 401]));
    }
}

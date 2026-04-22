<?php

declare(strict_types=1);

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Architecture\Middleware\ValidateAuthScopes;
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

       Route::middleware([ValidateAuthScopes::class . ':module:read'])
        ->get('/test/protected', function (\Illuminate\Http\Request $request) {
            return response()->json([
                'ok' => true,
                'user_id' => $request->attributes->get('oauth_user_id'),
                'client_id' => $request->attributes->get('oauth_client_id'),
                'scopes' => $request->attributes->get('oauth_scopes'),
            ]);
        });
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

    public function test_protected_route_with_valid_scope(): void
    {
        /* =========================================================
         * STEP 1: LOGIN
         * ========================================================= */
        $loginResponse = $this->postJson('/auth/login', [
            'login' => 'john',
            'password' => 'secret',
            'clientId' => 'nxsfr',
        ]);

        $ticket = $loginResponse->json('authTicket');

        /* =========================================================
         * STEP 2: AUTHORIZE (scope: module:read)
         * ========================================================= */
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

        /* =========================================================
         * STEP 3: TOKEN
         * ========================================================= */
        $tokenResponse = $this->post('/auth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'redirect_uri' => 'http://127.0.0.1',
            'code' => $authCode,
        ]);

        $data = json_decode($tokenResponse->getContent(), true);
        $accessToken = $data['access_token'];

        /* =========================================================
         * STEP 4: CALL PROTECTED ROUTE (middleware tested here)
         * ========================================================= */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->getJson('/test/protected');

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
        ]);

        // ✅ Ensure middleware injected attributes
        $this->assertEquals('user-123', $response->json('user_id'));
        $this->assertEquals('nxsfr', $response->json('client_id'));
        $this->assertContains('module:read', $response->json('scopes'));
    }

    public function test_protected_route_with_invalid_scope_returns_403(): void
    {
        // Same flow, but request DIFFERENT scope
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
            'scope' => 'module:write', // ❌ different scope
            'state' => 'xyz',
            'authTicket' => $ticket,
        ]));

        $location = $authorizeResponse->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $queryParams);

        $authCode = $queryParams['code'];

        $tokenResponse = $this->post('/auth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'redirect_uri' => 'http://127.0.0.1',
            'code' => $authCode,
        ]);

        $accessToken = json_decode($tokenResponse->getContent(), true)['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->getJson('/test/protected');

        $response->assertStatus(403);
    }

    public function test_protected_route_without_token_returns_401(): void
    {
        $response = $this->getJson('/test/protected');

        $response->assertStatus(401);
    }

    public function test_protected_route_with_invalid_token_returns_403(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/test/protected');

        $response->assertStatus(403);
    }

    public function test_refresh_token_flow_returns_new_access_token(): void
    {
        /* =========================================================
         * STEP 1: LOGIN
         * ========================================================= */
        $loginResponse = $this->postJson('/auth/login', [
            'login' => 'john',
            'password' => 'secret',
            'clientId' => 'nxsfr',
        ]);

        $ticket = $loginResponse->json('authTicket');

        /* =========================================================
         * STEP 2: AUTHORIZE
         * ========================================================= */
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

        /* =========================================================
         * STEP 3: TOKEN (initial)
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

        $accessToken1 = $data['access_token'];
        $refreshToken = $data['refresh_token'];

        $this->assertNotEmpty($refreshToken);


        /* =========================================================
         * STEP 4: REFRESH TOKEN
         * ========================================================= */
        $refreshResponse = $this->post('/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'refresh_token' => $refreshToken,
            // optional:
            // 'scope' => 'module:read',
        ]);

        $refreshResponse->assertStatus(200);

        $refreshData = json_decode($refreshResponse->getContent(), true);

        /* =========================================================
         * ASSERTIONS
         * ========================================================= */

        // structure
        $this->assertArrayHasKey('access_token', $refreshData);
        $this->assertArrayHasKey('refresh_token', $refreshData);
        $this->assertArrayHasKey('expires_in', $refreshData);
        $this->assertEquals('Bearer', $refreshData['token_type']);

        // new tokens should be different
        $this->assertNotEquals($accessToken1, $refreshData['access_token']);

        // depending on implementation (rotation or reuse)
        $this->assertNotEmpty($refreshData['refresh_token']);

        /* =========================================================
         * STEP 5: USE NEW ACCESS TOKEN
         * ========================================================= */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $refreshData['access_token'],
        ])->getJson('/test/protected');

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
        ]);
    }

    public function test_refresh_token_with_invalid_token_fails(): void
    {
        $response = $this->post('/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'refresh_token' => 'invalid-token',
        ]);

        $response->assertStatus(400);
    }

    public function test_refresh_token_is_one_time_use_and_rotates_correctly(): void
    {
        /* =========================================================
         * STEP 1: LOGIN
         * ========================================================= */
        $loginResponse = $this->postJson('/auth/login', [
            'login' => 'john',
            'password' => 'secret',
            'clientId' => 'nxsfr',
        ]);

        $ticket = $loginResponse->json('authTicket');

        /* =========================================================
         * STEP 2: AUTHORIZE
         * ========================================================= */
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

        /* =========================================================
         * STEP 3: INITIAL TOKEN
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

        $refreshToken1 = $data['refresh_token'];

        /* =========================================================
         * STEP 4: FIRST REFRESH (valid)
         * ========================================================= */
        $refreshResponse1 = $this->post('/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'refresh_token' => $refreshToken1,
        ]);

        $refreshResponse1->assertStatus(200);

        $data1 = json_decode($refreshResponse1->getContent(), true);

        $accessToken2 = $data1['access_token'];
        $refreshToken2 = $data1['refresh_token'];

        $this->assertNotEquals($refreshToken1, $refreshToken2);

        /* =========================================================
         * STEP 5: REUSE OLD REFRESH TOKEN (must fail)
         * ========================================================= */
        $refreshResponse2 = $this->post('/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'refresh_token' => $refreshToken1,
        ]);

        $refreshResponse2->assertStatus(400);

        /* =========================================================
         * STEP 6: USE NEW REFRESH TOKEN (must succeed)
         * ========================================================= */
        $refreshResponse3 = $this->post('/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => 'nxsfr',
            'client_secret' => 'secret',
            'refresh_token' => $refreshToken2,
        ]);

        $refreshResponse3->assertStatus(200);

        $data3 = json_decode($refreshResponse3->getContent(), true);

        $this->assertArrayHasKey('access_token', $data3);
        $this->assertArrayHasKey('refresh_token', $data3);
        $this->assertNotEquals($refreshToken2, $data3['refresh_token']);
    }
}

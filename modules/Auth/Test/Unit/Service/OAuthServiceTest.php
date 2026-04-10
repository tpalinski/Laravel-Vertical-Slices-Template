<?php

declare(strict_types=1);

namespace Modules\Auth\Test\Unit\Service;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Modules\Auth\Domain\Entity\User\UserEntity;
use Modules\Auth\Domain\Exception\User\InvalidClientException;
use Modules\Auth\Domain\Exception\User\UserNotAuthenticatedException;
use Modules\Auth\Domain\Factory\OAuthServerFactory;
use Modules\Auth\Domain\Service\OAuthService;
use Modules\Auth\Domain\Service\UserCredentials\UserCredentialsServiceInterface;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Mockery;

class OAuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /* =========================================================
     * authorize() success
     * ========================================================= */

    public function test_authorize_success(): void
    {
        $factory = Mockery::mock(OAuthServerFactory::class);
        $userService = Mockery::mock(UserCredentialsServiceInterface::class);

        $authServer = Mockery::mock(AuthorizationServer::class);

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = new Response();

        $authRequest = Mockery::mock(AuthorizationRequest::class);

        $factory->shouldReceive('build')
            ->once()
            ->andReturn($authServer);

        $authServer->shouldReceive('validateAuthorizationRequest')
            ->once()
            ->with($request)
            ->andReturn($authRequest);

        $userService->shouldReceive('isUserAuthenticated')
            ->once()
            ->with('ticket-123', 'client-1')
            ->andReturn('user-123');

        $authRequest->shouldReceive('setUser')
            ->once()
            ->with(Mockery::on(fn ($u) => $u instanceof UserEntity));

        $authRequest->shouldReceive('setAuthorizationApproved')
            ->once()
            ->with(true);

        $authServer->shouldReceive('completeAuthorizationRequest')
            ->once()
            ->with($authRequest, $response)
            ->andReturn($response);

        $service = new OAuthService($factory, $userService);

        $result = $service->authorize(
            'ticket-123',
            'client-1',
            $request,
            $response
        );

        $this->assertSame($response, $result);
    }

    /* =========================================================
     * authorize() throws: invalid client
     * ========================================================= */

    public function test_authorize_throws_invalid_client(): void
    {
        $factory = Mockery::mock(OAuthServerFactory::class);
        $userService = Mockery::mock(UserCredentialsServiceInterface::class);

        $authServer = Mockery::mock(AuthorizationServer::class);

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = new Response();

        $authRequest = Mockery::mock(AuthorizationRequest::class);

        $factory->shouldReceive('build')->andReturn($authServer);

        $authServer->shouldReceive('validateAuthorizationRequest')
            ->andReturn($authRequest);

        $userService->shouldReceive('isUserAuthenticated')
            ->andThrow(new InvalidClientException('invalid client'));

        $this->expectException(InvalidClientException::class);

        $service = new OAuthService($factory, $userService);

        $service->authorize(
            'ticket-123',
            'client-1',
            $request,
            $response
        );
    }

    /* =========================================================
     * authorize() throws: not authenticated
     * ========================================================= */

    public function test_authorize_throws_user_not_authenticated(): void
    {
        $factory = Mockery::mock(OAuthServerFactory::class);
        $userService = Mockery::mock(UserCredentialsServiceInterface::class);

        $authServer = Mockery::mock(AuthorizationServer::class);

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = new Response();

        $authRequest = Mockery::mock(AuthorizationRequest::class);

        $factory->shouldReceive('build')->andReturn($authServer);

        $authServer->shouldReceive('validateAuthorizationRequest')
            ->andReturn($authRequest);

        $userService->shouldReceive('isUserAuthenticated')
            ->andThrow(new UserNotAuthenticatedException('not authenticated'));

        $this->expectException(UserNotAuthenticatedException::class);

        $service = new OAuthService($factory, $userService);

        $service->authorize(
            'ticket-123',
            'client-1',
            $request,
            $response
        );
    }

    /* =========================================================
     * issueToken()
     * ========================================================= */

    public function test_issue_token_delegates_to_server(): void
    {
        $factory = Mockery::mock(OAuthServerFactory::class);
        $userService = Mockery::mock(UserCredentialsServiceInterface::class);

        $authServer = Mockery::mock(AuthorizationServer::class);

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = new Response();

        $factory->shouldReceive('build')
            ->once()
            ->andReturn($authServer);

        $authServer->shouldReceive('respondToAccessTokenRequest')
            ->once()
            ->with($request, $response)
            ->andReturn($response);

        $service = new OAuthService($factory, $userService);

        $result = $service->issueToken($request, $response);

        $this->assertSame($response, $result);
    }
}

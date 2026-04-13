<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Middleware;

use Closure;
use Core\Exception\HttpException\HttpForbiddenException;
use Core\Exception\HttpException\HttpUnauthorizedException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Modules\Auth\Architecture\Utils\RequestConverter;
use Modules\Auth\Domain\Factory\ResourceServerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateAuthScopes {

    private readonly ResourceServer $resourceServer;
    private readonly RequestConverter $converter;

    public function __construct(ResourceServerFactory $factory, RequestConverter $converter) {
        $this->resourceServer = $factory->build();
        $this->converter = $converter;
    }

    public function handle(Request $request, Closure $next, string $scope): Response {
        if (!$request->headers->has('Authorization')) {
            throw new HttpUnauthorizedException("Please provide an authorization token");
        }
        $req = $this->converter->convertToPsrRequest($request);
        try {
            $req = $this->resourceServer->validateAuthenticatedRequest($req);
        } catch (OAuthServerException $e) {
            throw new HttpForbiddenException($e->getMessage());
        }
        $body = $req->getAttributes();
        $scopes = $body['oauth_scopes'];
        if (array_search($scope, $scopes, true) === false) {
            throw new HttpForbiddenException('You do not have permissions to access this resource');
        }
        $request->attributes->set('oauth_scopes', $scopes);
        $request->attributes->set('oauth_access_token_id', $body['oauth_access_token_id']);
        $request->attributes->set('oauth_client_id', $body['oauth_client_id']);
        $request->attributes->set('oauth_user_id', $body['oauth_user_id']);
        return $next($request);
    }
}

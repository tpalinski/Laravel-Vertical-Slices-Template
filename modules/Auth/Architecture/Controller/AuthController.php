<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Controller;

use Core\Exception\HttpException\HttpNotFoundException;
use Core\Exception\HttpException\HttpUnauthorizedException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Auth\Architecture\Request\AuthorizeRequest;
use Modules\Auth\Domain\Exception\User\InvalidPasswordException;
use Modules\Auth\Domain\Exception\User\NonexistentUserException;
use Modules\Auth\Domain\Service\AuthServiceInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller {

    public function __construct(
        private AuthServiceInterface $authService,
    ) {}

    private function convertToPsrRequest(Request $request): ServerRequestInterface {
        $psr17Factory = new Psr17Factory();

        $psrRequest = $psr17Factory->createServerRequest(
            $request->method(),
            $request->fullUrl()
        );

        foreach ($request->headers->all() as $key => $values) {
            foreach ($values as $value) {
                $psrRequest = $psrRequest->withHeader($key, $value);
            }
        }

        $psrRequest = $psrRequest->withQueryParams($request->query->all());
        $psrRequest = $psrRequest->withParsedBody($request->post());

        return $psrRequest;
    }

    private function convertToLaravelResponse(Psr7Response $psrResponse): Response {
        $body = (string) $psrResponse->getBody();

        $response = new Response(
            $body,
            $psrResponse->getStatusCode()
        );
        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $response->headers->set($name, $value, false);
            }
        }

        return $response;
    }

    public function postToken(Request $request) {
        $req = $this->convertToPsrRequest($request);
        $res = $this->authService->issueToken($req, new Psr7Response());
        return $this->convertToLaravelResponse($res);
    }

    public function postAuthorize(AuthorizeRequest $request) {
        $password = $request['password'];
        $login = $request['login'];
        $req = $this->convertToPsrRequest($request);
        try {
            $res = $this->authService->authorize($login, $password, $req, new Psr7Response());
        } catch (InvalidPasswordException $e) {
            throw new HttpUnauthorizedException("Invalid user password");
        } catch (NonexistentUserException $e) {
            throw new HttpNotFoundException("No such user exists");
        }
        return $this->convertToLaravelResponse($res);
    }
}

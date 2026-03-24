<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
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

        $psrRequest = $psrRequest->withParsedBody($request->all());
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
}

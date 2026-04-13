<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Utils;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestConverter {

    public function convertToPsrRequest(Request $request): ServerRequestInterface {
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

    public function convertToLaravelResponse(Psr7Response $psrResponse): Response {
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


    public function convertToLaravelRequest(ServerRequestInterface $psrRequest): Request
    {
        // Create Symfony request first (Laravel extends it)
        $symfonyRequest = new SymfonyRequest(
            $psrRequest->getQueryParams(),
            $psrRequest->getParsedBody() ?? [],
            [], // attributes
            $psrRequest->getCookieParams(),
            [], // files (you can map uploaded files separately if needed)
            $psrRequest->getServerParams(),
            (string) $psrRequest->getBody()
        );

        // Set headers
        foreach ($psrRequest->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $symfonyRequest->headers->set($name, $value, false);
            }
        }

        // Set method
        $symfonyRequest->setMethod($psrRequest->getMethod());

        // Set URI components
        $uri = $psrRequest->getUri();
        $symfonyRequest->server->set('REQUEST_URI', $uri->getPath());
        $symfonyRequest->server->set('QUERY_STRING', $uri->getQuery());

        // Convert to Laravel request
        return Request::createFromBase($symfonyRequest);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Auth\Architecture\Controller;

use Core\Exception\HttpException\HttpBadRequestException;
use Core\Exception\HttpException\HttpNotFoundException;
use Core\Exception\HttpException\HttpUnauthorizedException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use League\OAuth2\Server\Exception\OAuthServerException;
use Modules\Auth\Architecture\Request\AuthorizeRequest;
use Modules\Auth\Architecture\Request\LoginRequest;
use Modules\Auth\Architecture\Response\AuthTicketResponse;
use Modules\Auth\Domain\DTO\UserCredentials\LoginDTO;
use Modules\Auth\Domain\Exception\Oauth\RequestValidationException;
use Modules\Auth\Domain\Exception\User\InvalidClientException;
use Modules\Auth\Domain\Exception\User\InvalidPasswordException;
use Modules\Auth\Domain\Exception\User\NonexistentUserException;
use Modules\Auth\Domain\Exception\User\UserNotAuthenticatedException;
use Modules\Auth\Domain\Service\AuthServiceInterface;
use Modules\Auth\Domain\Service\UserCredentials\UserCredentialsServiceInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller {

    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly UserCredentialsServiceInterface $userService,
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
        try {
            $res = $this->authService->issueToken($req, new Psr7Response());
        } catch (OAuthServerException $e) {
            throw new HttpBadRequestException($e->getMessage());
        }
        return $this->convertToLaravelResponse($res);
    }

    public function getAuthorize(AuthorizeRequest $request) {
        $authTicket = $request->query('authTicket', '');
        $clientId = $request->query('client_id', '');
        $req = $this->convertToPsrRequest($request);
        try {
            $res = $this->authService->authorize($authTicket, $clientId, $req, new Psr7Response());
        } catch (UserNotAuthenticatedException $e) {
            throw new HttpUnauthorizedException($e->getMessage());
        } catch (InvalidClientException $e) {
            throw new HttpUnauthorizedException($e->getMessage());
        } catch (RequestValidationException $e) {
            throw new HttpUnauthorizedException($e->getMessage());
        }
        return $this->convertToLaravelResponse($res);
    }

    public function postLogin(LoginRequest $request) {
        $dto = new LoginDTO(
            login: $request->login,
            password: $request->password,
            clientId: $request->clientId,
        );
        try {
            $authTicket = $this->userService->validateCredentials($dto);
        } catch (InvalidPasswordException $e) {
            throw new HttpUnauthorizedException($e->getMessage());
        } catch (NonexistentUserException $e) {
            throw new HttpNotFoundException($e->getMessage());
        }
        return new AuthTicketResponse($authTicket);
    }
}

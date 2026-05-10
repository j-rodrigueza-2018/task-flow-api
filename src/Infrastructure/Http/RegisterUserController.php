<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\RegisterUserUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

final class RegisterUserController
{
    public function __construct(
        private readonly RegisterUserUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $request_data = $request->getParsedBody();

        $nickname = $request_data['nickname'] ?? '';
        $email = $request_data['email'] ?? '';
        $password = $request_data['password'] ?? '';

        try {
            $this->use_case->execute($nickname, $email, $password);
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'User registered successfully.'
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (InvalidArgumentException $exception) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $exception->getMessage()
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        } catch (RuntimeException $exception) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $exception->getMessage()
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(409);
        }
    }
}

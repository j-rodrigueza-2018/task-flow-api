<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\UseCase\User\LoginUserUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class LoginUserController
{
    public function __construct(
        private readonly LoginUserUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        $request_data = $request->getParsedBody();

        $email = $request_data['email'] ?? '';
        $password = $request_data['password'] ?? '';

        try {
            $token = $this->use_case->execute($email, $password);
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'User logged in successfully.',
                'token' => $token
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\InvalidArgumentException $exception) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $exception->getMessage()
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
    }
}

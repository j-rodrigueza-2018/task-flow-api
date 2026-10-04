<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\User;

use App\Application\UseCase\User\LoginUserUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;

final class LoginUserController
{
    public function __construct(
        private readonly LoginUserUseCase $use_case
    ) {}

    #[OA\Post(
        path: '/api/login',
        summary: 'Authenticate user and get a JWT token.',
        description: 'Verifies the user credentials and returns a JWT token for accessing protected routes.',
        tags: ['Users']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SuperSecret123!')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'User logged in successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'User logged in successfully.'),
                new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized (invalid credentials).',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid email or password.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response): Response
    {
        $request_data = $request->getParsedBody();

        $email = $request_data['email'] ?? '';
        $password = $request_data['password'] ?? '';

        try {
            $token = $this->use_case->execute($email, $password);
            $response->getBody()->write(
                json_encode([
                    'status' => 'success',
                    'message' => 'User logged in successfully.',
                    'token' => $token
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (InvalidArgumentException $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\User;

use App\Application\UseCase\User\RegisterUserUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use RuntimeException;

final class RegisterUserController
{
    public function __construct(
        private readonly RegisterUserUseCase $use_case
    ) {}

    #[OA\Post(
        path: '/api/users',
        summary: 'Register a new user.',
        description: 'Creates a new user account in the system.',
        tags: ['Users']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nickname', 'email', 'password'],
            properties: [
                new OA\Property(property: 'nickname', type: 'string', example: 'johndoe', description: 'The desired nickname (4-20 chars).'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com', description: 'A valid email address.'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SuperSecret123!', description: 'The user password (min 8 chars).')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User registered successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'User registered successfully.')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation error (e.g., short password or invalid email format).',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The password must be at least 8 characters long.')
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: 'Conflict error (e.g., email already registered).',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'A user with this email already exists.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response): Response
    {
        $request_data = $request->getParsedBody();

        $nickname = $request_data['nickname'] ?? '';
        $email = $request_data['email'] ?? '';
        $password = $request_data['password'] ?? '';

        try {
            $this->use_case->execute($nickname, $email, $password);
            $response->getBody()->write(
                json_encode([
                    'status' => 'success',
                    'message' => 'User registered successfully.'
                ])
            );

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

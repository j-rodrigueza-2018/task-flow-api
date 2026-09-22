<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\DeleteUserFromTaskUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class DeleteUserFromTaskController
{
    public function __construct(
        private readonly DeleteUserFromTaskUseCase $use_case
    ) {}

    #[OA\Delete(
        path: '/api/private/tasks/{id}/users/{user_id}',
        summary: 'Remove a user from a task.',
        description: 'Removes the assignment of a specific user from a task.',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The unique identifier of the task.',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Parameter(
        name: 'user_id',
        in: 'path',
        required: true,
        description: 'The unique identifier of the user to remove.',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'User removed from task successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'User removed from the task successfully.')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request or validation error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The user is not related to this task.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred while deleting the user from the task.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $this->use_case->execute(
                task_id: strval($args['id'] ?? ''),
                user_id: strval($args['user_id'] ?? ''),
                requester_id: strval($jwt_payload->sub)
            );

            $response->getBody()->write(
                json_encode([
                    'status' => 'success',
                    'message' => 'User removed from the task successfully.'
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
                ->withStatus(400);
        } catch (Throwable $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => 'An unexpected error occurred while deleting the user from the task.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

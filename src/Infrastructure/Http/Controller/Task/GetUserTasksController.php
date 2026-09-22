<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\GetUserTasksUseCase;
use App\Domain\Entity\Task;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class GetUserTasksController
{
    public function __construct(
        private readonly GetUserTasksUseCase $use_case
    ) {}

    #[OA\Get(
        path: '/api/private/tasks',
        summary: 'Get user tasks.',
        description: 'Retrieve all tasks for a specific user.',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'User tasks retrieved successfully.'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: Task::class)
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
    )]
    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $tasks = $this->use_case->execute($jwt_payload->sub);

            $tasks_data = array_map(fn($task) => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s')
            ], $tasks);

            $payload = json_encode([
                'status' => 'success',
                'message' => 'User tasks retrieved successfully.',
                'data' => $tasks_data
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Throwable $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => 'An error occurred while retrieving user tasks.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

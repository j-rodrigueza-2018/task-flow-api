<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\GetTaskUseCase;
use App\Domain\Entity\Task;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class GetTaskController
{
    public function __construct(
        private readonly GetTaskUseCase $use_case
    ) {}

    #[OA\Get(
        path: '/api/private/tasks/{id}',
        summary: 'Get a specific task',
        description: 'Retrieve details of a specific task by its ID',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The unique identifier of the task',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Task retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'data', ref: Task::class)
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Task not found or invalid ID',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Task not found or unauthorized.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error'
    )]
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $task = $this->use_case->execute(
                task_id: strval($args['id']),
                requester_id: strval($jwt_payload->sub)
            );

            $payload = json_encode([
                'status' => 'success',
                'data' => [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'status' => $task->getStatus(),
                    'board_id' => $task->getBoardId(),
                    'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s')
                ]
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (InvalidArgumentException $exception) {
            $payload = json_encode([
                'status' => 'error',
                'message' => $exception->getMessage()
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        } catch (Throwable $exception) {
            $payload = json_encode([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the task.',
                'debug' => $exception->getMessage()
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

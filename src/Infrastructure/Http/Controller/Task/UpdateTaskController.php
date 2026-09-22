<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\UpdateTaskUseCase;
use App\Domain\Entity\Task;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class UpdateTaskController
{
    public function __construct(
        private readonly UpdateTaskUseCase $use_case
    ) {}

    #[OA\Patch(
        path: '/api/private/tasks/{id}',
        summary: 'Update an existing task.',
        description: 'Update task properties such as title, description, status, or board assignment.',
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
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Updated task title.',
                    description: 'The new title for the task.'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    nullable: true,
                    example: 'Updated description details.',
                    description: 'The new description for the task.'
                ),
                new OA\Property(
                    property: 'status',
                    type: 'string',
                    enum: ['pending', 'in_progress', 'completed'],
                    example: 'in_progress',
                    description: 'The new status of the task.'
                ),
                new OA\Property(
                    property: 'board_id',
                    type: 'string',
                    format: 'uuid',
                    example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                    description: 'The ID of the new board to move the task to.'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Task updated successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Task updated successfully.'),
                new OA\Property(property: 'data', ref: Task::class)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request or validation error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The title must be between 3 and 255 characters long.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An error occurred while updating the task.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');
            $task_id = $args['id'];

            $request_data = (array) $request->getParsedBody();
            $task = $this->use_case->execute(
                task_id: $task_id,
                requester_id: $jwt_payload->sub,
                title: $request_data['title'] ?? null,
                description: $request_data['description'] ?? null,
                status: $request_data['status'] ?? null,
                board_id: $request_data['board_id'] ?? null
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'Task updated successfully.',
                'data' => [
                    'id' => $task->getId(),
                    'board_id' => $task->getBoardId(),
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'status' => $task->getStatus(),
                    'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s')
                ]
            ]);

            $response->getBody()->write($payload);

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
                    'message' => 'An error occurred while updating the task.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\CreateTaskUseCase;
use App\Domain\Entity\Task;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class CreateTaskController
{
    public function __construct(
        private readonly CreateTaskUseCase $use_case
    ) {}

    #[OA\Post(
        path: '/api/private/tasks',
        summary: 'Create a new task.',
        description: 'Creates a new task and assigns it to a specific board.',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['board_id', 'title'],
            properties: [
                new OA\Property(
                    property: 'board_id',
                    type: 'string',
                    format: 'uuid',
                    example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                    description: 'ID of the board where the task will be created.'
                ),
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Configure database connection.',
                    description: 'Title of the task.'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    nullable: true,
                    example: 'Review the credentials in the .env file.',
                    description: 'Optional details for the task.'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Task created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Task created successfully.'),
                new OA\Property(property: 'data', ref: Task::class)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request or validation error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The title cannot be empty.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error'
    )]
    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $data = (array) $request->getParsedBody();

            $task = $this->use_case->execute(
                user_id: $jwt_payload->sub ?? '',
                board_id: $data['board_id'] ?? '',
                title: $data['title'] ?? '',
                description: $data['description'] ?? null
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'Task created successfully.',
                'data' => [
                    'id' => $task->getId(),
                    'board_id' => $task->getBoardId(),
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'status' => $task->getStatus(),
                    'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s')
                ]
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
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
                    'message' => 'Internal server error.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

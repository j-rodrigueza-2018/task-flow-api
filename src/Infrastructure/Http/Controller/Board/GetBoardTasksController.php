<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\GetBoardTasksUseCase;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class GetBoardTasksController
{
    public function __construct(
        private readonly GetBoardTasksUseCase $use_case
    ) {}

    #[OA\Get(
        path: '/api/private/boards/{id}/tasks',
        summary: 'Get all tasks for a board.',
        description: 'Retrieves a list of all tasks belonging to a specific board. The requester must be a member of the board.',
        tags: ['Boards'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The unique identifier of the board.',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Board tasks retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Board tasks retrieved successfully.'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'title', type: 'string'),
                            new OA\Property(property: 'description', type: 'string', nullable: true),
                            new OA\Property(property: 'status', type: 'string'),
                            new OA\Property(property: 'board_id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request (e.g., empty board id).',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The board_id cannot be empty.')
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Permission denied.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Requester does not have permission to access this board.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An error occurred while retrieving board tasks.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $tasks = $this->use_case->execute(
                board_id: strval($args['id']),
                requester_id: strval($jwt_payload->sub)
            );

            $tasks_data = array_map(fn($task) => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'board_id' => $task->getBoardId(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ], $tasks);

            $payload = json_encode([
                'status' => 'success',
                'message' => 'Board tasks retrieved successfully.',
                'data' => $tasks_data
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
        } catch (DomainException $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(403);
        } catch (Throwable $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => 'An error occurred while retrieving board tasks.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

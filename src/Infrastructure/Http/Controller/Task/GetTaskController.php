<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\GetTaskUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class GetTaskController
{
    public function __construct(
        private readonly GetTaskUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $task = $this->use_case->execute(
                task_id: strval($args['id']),
                user_id: strval($jwt_payload->sub)
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

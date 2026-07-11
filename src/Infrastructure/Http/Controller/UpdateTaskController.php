<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\UseCase\Task\UpdateTaskUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class UpdateTaskController
{
    public function __construct(
        private readonly UpdateTaskUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');
            $task_id = $args['id'];

            $request_data = $request->getParsedBody();
            $task = $this->use_case->execute(
                task_id: $task_id,
                user_id: $jwt_payload->sub,
                title: $request_data['title'] ?? null,
                description: $request_data['description'] ?? null,
                status: $request_data['status'] ?? null
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'Task updated successfully.',
                'data' => [
                    'id' => $task->getId(),
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

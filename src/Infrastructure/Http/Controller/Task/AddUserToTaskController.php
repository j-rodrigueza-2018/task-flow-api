<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\AddUserToTaskUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class AddUserToTaskController
{
    public function __construct(
        private readonly AddUserToTaskUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $request_data = (array) $request->getParsedBody();

            if (!array_key_exists('user_id', $request_data) || empty($request_data['user_id'])) {
                throw new InvalidArgumentException('Field "user_id" is required.');
            }

            $task_user = $this->use_case->execute(
                task_id: $args['id'],
                user_id: strval($request_data['user_id'])
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'User added to task successfully.',
                'data' => [
                    'id' => $task_user->getId(),
                    'task_id' => $task_user->getTaskId(),
                    'user_id' => $task_user->getUserId(),
                    'created_at' => $task_user->getCreatedAt()->format('Y-m-d H:i:s')
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
                    'message' => 'An unexpected error occurred while adding the user to the task.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

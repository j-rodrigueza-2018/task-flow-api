<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\DeleteUserFromTaskUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class DeleteUserFromTaskController
{
    public function __construct(
        private readonly DeleteUserFromTaskUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $this->use_case->execute(
                task_id: $args['id'] ?? '',
                user_id: $args['user_id'] ?? '',
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

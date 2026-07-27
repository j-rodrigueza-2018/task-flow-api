<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\DeleteUserFromBoardUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class DeleteUserFromBoardController
{
    public function __construct(
        private readonly DeleteUserFromBoardUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $this->use_case->execute($args['id'], $args['user_id']);

            $response->getBody()->write(
                json_encode([
                    'status' => 'success',
                    'message' => 'User deleted from the board successfully.'
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
                ->withStatus(404);
        } catch (Throwable $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => 'An unexpected error ocurred while deleting the user from the board.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

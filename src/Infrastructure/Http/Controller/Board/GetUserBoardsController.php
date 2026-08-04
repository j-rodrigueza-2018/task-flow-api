<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\GetUserBoardsUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class GetUserBoardsController
{
    public function __construct(
        private readonly GetUserBoardsUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $boards = $this->use_case->execute($jwt_payload->sub);

            $boards_data = array_map(fn($board) => [
                'id' => $board->getId(),
                'name' => $board->getName(),
                'description' => $board->getDescription(),
                'created_at' => $board->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $board->getUpdatedAt()->format('Y-m-d H:i:s')
            ], $boards);

            $payload = json_encode([
                'status' => 'success',
                'message' => 'User boards retrieved successfully.',
                'data' => $boards_data
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Throwable $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => 'An error ocurred while retrieving user boards.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

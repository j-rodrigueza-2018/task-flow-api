<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\UpdateBoardUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class UpdateBoardController
{
    public function __construct(
        private readonly UpdateBoardUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args)
    {
        try {
            $request_data = (array) $request->getParsedBody();
            $board = $this->use_case->execute(
                board_id: $args['id'],
                name: array_key_exists('name', $request_data) ? strval($request_data['name']) : null,
                description: array_key_exists('description', $request_data) ? strval($request_data['description']) : null
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'Board updated successfully.',
                'data' => [
                    'id' => $board->getId(),
                    'name' => $board->getName(),
                    'description' => $board->getDescription(),
                    'created_at' => $board->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $board->getUpdatedAt()->format('Y-m-d H:i:s')
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
                    'message' => 'An error occurred while updating the board.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

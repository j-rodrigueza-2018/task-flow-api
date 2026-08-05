<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\GetBoardUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class GetBoardController
{
    public function __construct(
        private readonly GetBoardUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $board = $this->use_case->execute(
                board_id: strval($args['id']),
                user_id: strval($jwt_payload->sub)
            );

            $payload = json_encode([
                'status' => 'success',
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
                'message' => 'An error occurred while retrieving the board.',
                'debug' => $exception->getMessage()
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

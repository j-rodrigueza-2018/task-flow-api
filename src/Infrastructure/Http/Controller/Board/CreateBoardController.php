<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\CreateBoardUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class CreateBoardController
{
    public function __construct(
        private readonly CreateBoardUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');
            $data = $request->getParsedBody();

            $board = $this->use_case->execute(
                user_id: $jwt_payload->sub,
                name: $data['name'] ?? '',
                description: $data['description'] ?? null
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'Board created successfully.',
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

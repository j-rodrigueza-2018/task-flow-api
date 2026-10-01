<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\GetUserBoardsUseCase;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class GetUserBoardsController
{
    public function __construct(
        private readonly GetUserBoardsUseCase $use_case
    ) {}

    #[OA\Get(
        path: '/api/private/boards',
        summary: 'Get all boards for the authenticated user.',
        description: 'Retrieves a list of all boards the authenticated user belongs to.',
        tags: ['Boards'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'User boards retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'User boards retrieved successfully.'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'description', type: 'string', nullable: true),
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
        description: 'Bad request.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The requester_id cannot be empty.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An error occurred while retrieving user boards.')
            ]
        )
    )]
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
                    'message' => 'An error occurred while retrieving user boards.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

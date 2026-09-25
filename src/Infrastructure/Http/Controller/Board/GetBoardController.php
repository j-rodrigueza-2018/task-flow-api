<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\GetBoardUseCase;
use App\Domain\Entity\Board;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class GetBoardController
{
    public function __construct(
        private readonly GetBoardUseCase $use_case
    ) {}

    #[OA\Get(
        path: '/api/private/boards/{id}',
        summary: 'Get a specific board.',
        description: 'Retrieve the details of a specific board by its ID.',
        tags: ['Boards'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The unique identifier of the board.',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Board retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'data', ref: Board::class)
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Board not found or access denied.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Board not found.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An error occurred while retrieving the board.')
            ]
        )
    )]
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

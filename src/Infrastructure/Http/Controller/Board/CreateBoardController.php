<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\CreateBoardUseCase;
use App\Domain\Entity\Board;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class CreateBoardController
{
    public function __construct(
        private readonly CreateBoardUseCase $use_case
    ) {}

    #[OA\Post(
        path: '/api/private/boards',
        summary: 'Create a new board.',
        description: 'Creates a new board and assigns the requester as its owner.',
        tags: ['Boards'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Project Management Board',
                    description: 'The name of the board.'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    nullable: true,
                    example: 'This board is used for managing project tasks and milestones.',
                    description: 'Optional details for the board.'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Board created successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Board created successfully.'),
                new OA\Property(property: 'data', ref: Board::class)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request or validation error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The name must be between 10 and 255 characters long.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.'
    )]
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

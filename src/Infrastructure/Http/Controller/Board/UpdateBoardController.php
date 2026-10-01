<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\UpdateBoardUseCase;
use App\Domain\Entity\Board;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class UpdateBoardController
{
    public function __construct(
        private readonly UpdateBoardUseCase $use_case
    ) {}

    #[OA\Patch(
        path: '/api/private/boards/{id}',
        summary: 'Update an existing board.',
        description: 'Updates the name and/or description of a specific board. The requester must be a member of the board.',
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
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Updated Project Board',
                    description: 'The new name of the board.'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    nullable: true,
                    example: 'Updated description with new milestones.',
                    description: 'The new description of the board.'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Board updated successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Board updated successfully.'),
                new OA\Property(property: 'data', ref: Board::class)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request or permission denied.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Board not found. / Requester does not have permission to update this board.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An error occurred while updating the board.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response, array $args)
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');
            $request_data = (array) $request->getParsedBody();

            $board = $this->use_case->execute(
                board_id: strval($args['id'] ?? ''),
                requester_id: strval($jwt_payload->sub),
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

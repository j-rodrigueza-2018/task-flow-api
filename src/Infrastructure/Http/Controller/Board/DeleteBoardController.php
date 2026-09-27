<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\DeleteBoardUseCase;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class DeleteBoardController
{
    public function __construct(
        private readonly DeleteBoardUseCase $use_case
    ) {}

    #[OA\Delete(
        path: '/api/private/boards/{id}',
        summary: 'Delete a specific board.',
        description: 'Soft deletes a board. Requires OWNER privileges on the board.',
        tags: ['Boards'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The unique identifier of the board to delete.',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Board deleted successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Board deleted successfully.')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request or permission denied.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Requester does not have permission to delete this board.')
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: 'Conflict, board is already deleted.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'The board is already deleted.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred while deleting the board.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');

            $this->use_case->execute(
                board_id: strval($args['id'] ?? ''),
                requester_id: strval($jwt_payload->sub)
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'Board deleted successfully.'
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
        } catch (DomainException $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(409);
        } catch (Throwable $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => 'An unexpected error occurred while deleting the board.'
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

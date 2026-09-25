<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\AddUserToBoardUseCase;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;
use ValueError;

final class AddUserToBoardController
{
    public function __construct(
        private readonly AddUserToBoardUseCase $use_case
    ) {}

    #[OA\Post(
        path: '/api/private/boards/{id}/users',
        summary: 'Add a user to a board.',
        description: 'Assigns a user to a specific board with a given role. Requires ADMIN or OWNER privileges.',
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
        required: true,
        content: new OA\JsonContent(
            required: ['user_id', 'role'],
            properties: [
                new OA\Property(
                    property: 'user_id',
                    type: 'string',
                    format: 'uuid',
                    example: 'b1c2d3e4-f5g6-7890-hijk-lm1234567890',
                    description: 'The unique identifier of the user to add.'
                ),
                new OA\Property(
                    property: 'role',
                    type: 'string',
                    enum: BoardRole::class,
                    example: 'MEMBER',
                    description: 'The role to assign to the user on the board.'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User added to board successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'User added to board successfully.'),
                new OA\Property(property: 'data', ref: BoardUser::class)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request, missing fields, invalid role, or requester does not belong to the board.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Field "user_id" is required.')
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: 'Domain exception, such as the user already existing in the board or lacking permissions.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Requester does not have permission to add users to this board.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred while adding the user to the board.')
            ]
        )
    )]
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');
            $request_data = (array) $request->getParsedBody();

            $board_user = $this->use_case->execute(
                board_id: strval($args['id'] ?? ''),
                user_id: strval($request_data['user_id'] ?? ''),
                board_role: BoardRole::from(strval($request_data['role'] ?? '')),
                requester_id: strval($jwt_payload->sub)
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'User added to board successfully.',
                'data' => [
                    'id' => $board_user->getId(),
                    'board_id' => $board_user->getBoardId(),
                    'user_id' => $board_user->getUserId(),
                    'role' => $board_user->getRole()->value,
                    'created_at' => $board_user->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $board_user->getUpdatedAt()->format('Y-m-d H:i:s')
                ]
            ]);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (ValueError $exception) {
            $response->getBody()->write(
                json_encode([
                    'status' => 'error',
                    'message' => 'Invalid role provided. Please check the accepted roles.'
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
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
                    'message' => 'An unexpected error occurred while adding the user to the board.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

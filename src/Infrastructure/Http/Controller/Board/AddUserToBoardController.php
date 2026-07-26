<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Board;

use App\Application\UseCase\Board\AddUserToBoardUseCase;
use App\Domain\Enum\BoardRole;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use ValueError;

final class AddUserToBoardController
{
    public function __construct(
        private readonly AddUserToBoardUseCase $use_case
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $request_data = (array) $request->getParsedBody();

            if (!array_key_exists('user_id', $request_data) || empty($request_data['user_id'])) {
                throw new InvalidArgumentException('Field "user_id" is required.');
            }

            if (!array_key_exists('role', $request_data) || empty($request_data['role'])) {
                throw new InvalidArgumentException('Field "role" is required.');
            }

            $board_user = $this->use_case->execute(
                board_id: $args['id'],
                user_id: strval($request_data['user_id']),
                board_role: BoardRole::from(strval($request_data['role']))
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

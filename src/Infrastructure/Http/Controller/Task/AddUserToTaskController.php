<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Task;

use App\Application\UseCase\Task\AddUserToTaskUseCase;
use App\Domain\Entity\TaskUser;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OA;
use Throwable;

final class AddUserToTaskController
{
    public function __construct(
        private readonly AddUserToTaskUseCase $use_case
    ) {}

    #[OA\Post(
        path: '/api/private/tasks/{id}/users',
        summary: 'Add a user to a task.',
        description: 'Assigns a specific user to an existing task by their ID.',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The unique identifier of the task.',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['user_id'],
            properties: [
                new OA\Property(
                    property: 'user_id',
                    type: 'string',
                    format: 'uuid',
                    example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                    description: 'ID of the user to add to the task.'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User added to task successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'User added to task successfully.'),
                new OA\Property(property: 'data', ref: TaskUser::class)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request (e.g., missing user_id).',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Field "user_id" is required.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.'
    )]
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $jwt_payload = $request->getAttribute('jwt_payload');
            $request_data = (array) $request->getParsedBody();

            if (!array_key_exists('user_id', $request_data) || empty($request_data['user_id'])) {
                throw new InvalidArgumentException('Field "user_id" is required.');
            }

            $task_user = $this->use_case->execute(
                task_id: $args['id'],
                user_id: strval($request_data['user_id']),
                requester_id: $jwt_payload->sub
            );

            $payload = json_encode([
                'status' => 'success',
                'message' => 'User added to task successfully.',
                'data' => [
                    'id' => $task_user->getId(),
                    'task_id' => $task_user->getTaskId(),
                    'user_id' => $task_user->getUserId(),
                    'created_at' => $task_user->getCreatedAt()->format('Y-m-d H:i:s')
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
                    'message' => 'An unexpected error occurred while adding the user to the task.',
                    'debug' => $exception->getMessage()
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}

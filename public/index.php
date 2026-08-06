<?php

use App\Infrastructure\Http\Controller\Board\AddUserToBoardController;
use App\Infrastructure\Http\Controller\Board\CreateBoardController;
use App\Infrastructure\Http\Controller\Board\DeleteBoardController;
use App\Infrastructure\Http\Controller\Board\DeleteUserFromBoardController;
use App\Infrastructure\Http\Controller\Board\GetBoardController;
use App\Infrastructure\Http\Controller\Board\GetBoardTasksController;
use App\Infrastructure\Http\Controller\Board\GetUserBoardsController;
use App\Infrastructure\Http\Controller\Board\UpdateBoardController;
use App\Infrastructure\Http\Controller\Task\AddUserToTaskController;
use App\Infrastructure\Http\Controller\Task\CreateTaskController;
use App\Infrastructure\Http\Controller\Task\DeleteTaskController;
use App\Infrastructure\Http\Controller\Task\DeleteUserFromTaskController;
use App\Infrastructure\Http\Controller\Task\GetTaskController;
use App\Infrastructure\Http\Controller\Task\GetUserTasksController;
use App\Infrastructure\Http\Controller\User\LoginUserController;
use App\Infrastructure\Http\Controller\User\RegisterUserController;
use App\Infrastructure\Http\Controller\Task\UpdateTaskController;
use App\Infrastructure\Http\Middleware\AuthMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// 1. Container's configuration
$container_builder = new ContainerBuilder();
$container_builder->addDefinitions(__DIR__ . '/../config/dependencies.php');

$container = $container_builder->build();

// 2. Slim App's creation
AppFactory::setContainer($container);
$app = AppFactory::create();

// 3. Middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// 4. Routes
$app->post('/api/users', RegisterUserController::class);
$app->post('/api/login', LoginUserController::class);

$app->get('/api/health', function (Request $request, Response $response, $args) {
    $payload = json_encode([
        'status' => 'OK',
        'message' => 'TaskFlow API is alive and routing with Slim.',
        'version' => phpversion()
    ]);

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->group('/api/private', function (RouteCollectorProxy $group) {
    $group->get('/me', function (Request $request, Response $response) {
        $jwt_payload = $request->getAttribute('jwt_payload');

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Valid token. Access granted to protected route.',
            'user_id' => $jwt_payload->sub,
            'email' => $jwt_payload->email
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    // Board routes
    $group->get('/boards/{id}', GetBoardController::class);
    $group->get('/boards', GetUserBoardsController::class);
    $group->post('/boards', CreateBoardController::class);
    $group->get('/boards/{id}/tasks', GetBoardTasksController::class);
    $group->patch('/boards/{id}', UpdateBoardController::class);
    $group->delete('/boards/{id}', DeleteBoardController::class);
    $group->post('/boards/{id}/users', AddUserToBoardController::class);
    $group->delete('/boards/{id}/users/{user_id}', DeleteUserFromBoardController::class);

    // Task routes
    $group->get('/tasks/{id}', GetTaskController::class);
    $group->post('/tasks', CreateTaskController::class);
    $group->get('/tasks', GetUserTasksController::class);
    $group->patch('/tasks/{id}', UpdateTaskController::class);
    $group->delete('/tasks/{id}', DeleteTaskController::class);
    $group->post('/tasks/{id}/users', AddUserToTaskController::class);
    $group->delete('/tasks/{id}/users/{user_id}', DeleteUserFromTaskController::class);
})->add($container->get(AuthMiddleware::class));

$app->run();

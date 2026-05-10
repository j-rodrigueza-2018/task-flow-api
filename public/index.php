<?php

use App\Domain\Repository\UserRepository;
use App\Infrastructure\Http\RegisterUserController;
use App\Infrastructure\Persistence\PostgresUserRepository;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use function DI\autowire;

require __DIR__ . '/../vendor/autoload.php';

// 1. Container's configuration
$container_builder = new ContainerBuilder();
$container_builder->addDefinitions([
    PDO::class => function () {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $db_name = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        $dsn = "pgsql:host={$host};port={$port};dbname={$db_name}";

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    },

    UserRepository::class => autowire(PostgresUserRepository::class),
]);

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

$app->get('/api/health', function (Request $request, Response $response, $args) {
    $payload = json_encode([
        'status' => 'OK',
        'message' => 'TaskFlow API is alive and routing with Slim.',
        'version' => phpversion()
    ]);

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->run();

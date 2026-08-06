<?php

declare(strict_types=1);

use App\Application\UseCase\User\LoginUserUseCase;
use App\Domain\Repository\BoardRepository;
use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use App\Domain\Repository\TaskUserRepository;
use App\Domain\Repository\UserRepository;
use App\Infrastructure\Http\Middleware\AuthMiddleware;
use App\Infrastructure\Persistence\PostgresBoardRepository;
use App\Infrastructure\Persistence\PostgresBoardUserRepository;
use App\Infrastructure\Persistence\PostgresTaskRepository;
use App\Infrastructure\Persistence\PostgresTaskUserRepository;
use App\Infrastructure\Persistence\PostgresUserRepository;

use function DI\autowire;

return [
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
    TaskRepository::class => autowire(PostgresTaskRepository::class),
    TaskUserRepository::class => autowire(PostgresTaskUserRepository::class),
    BoardRepository::class => autowire(PostgresBoardRepository::class),
    BoardUserRepository::class => autowire(PostgresBoardUserRepository::class),

    AuthMiddleware::class => autowire()->constructorParameter(
        'jwt_secret',
        getenv('JWT_SECRET')
    ),

    LoginUserUseCase::class => autowire()->constructorParameter(
        'jwt_secret',
        getenv('JWT_SECRET')
    )
];

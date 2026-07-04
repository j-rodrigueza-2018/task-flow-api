<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$db_name = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASS');

$data_source_name = "pgsql:host={$host};port={$port};dbname={$db_name}";

try {
    $pdo = new PDO(
        $data_source_name,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "Connected to the database successfully." . PHP_EOL;

    $migration_files = glob(__DIR__ . '/../database/migrations/*.sql');
    sort($migration_files);

    foreach ($migration_files as $migration_file) {
        $filename = basename($migration_file);
        echo "Processing migration: {$filename}" . PHP_EOL;

        $sql = file_get_contents($migration_file);
        $pdo->exec($sql);

        echo "Migration {$filename} executed successfully." . PHP_EOL;
    }
} catch (PDOException $exception) {
    echo "Database error: " . $exception->getMessage() . PHP_EOL;
    exit(1);
}

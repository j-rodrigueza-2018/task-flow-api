<?php

declare(strict_types=1);

namespace App\Infrastructure\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Task Flow API",
    version: "1.0.0",
    description: "API for managing tasks in the Task Flow application."
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
final class SwaggerGlobalConfig {}

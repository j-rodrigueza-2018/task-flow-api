<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $jwt_secret
    ) {}

    public function process(Request $request, RequestHandler $request_handler): Response
    {
        $auth_header = $request->getHeaderLine('Authorization');

        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return $this->unauthorizedResponse('Token missing or malformed.');
        }

        $token = $matches[1];
        
        try {
            $decoded_token = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            $request = $request->withAttribute('jwt_payload', $decoded_token);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Invalid or expired token: ' . $e->getMessage());
        }

        return $request_handler->handle($request);
    }

    private function unauthorizedResponse(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}

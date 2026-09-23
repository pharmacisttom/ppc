<?php
namespace App\Core;

/**
 * HTTP Response Helper
 */
class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        self::json([
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message
        ], $statusCode);
    }
}

<?php
namespace App\Core;

/**
 * Cross-Site Request Forgery (CSRF) Protection
 */
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Generate or return existing CSRF token
     */
    public static function token(): string
    {
        Session::start();
        $token = Session::get(self::TOKEN_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    /**
     * Render hidden HTML input field
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate incoming token from POST or Header
     */
    public static function validate(?string $token = null): bool
    {
        Session::start();
        $sessionToken = Session::get(self::TOKEN_KEY);
        if (!$sessionToken) {
            return false;
        }

        if ($token === null) {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (!$token || !is_string($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}

<?php
namespace App\Core;

/**
 * Application Core Bootstrapper & Autoloader
 */
class App
{
    public static function boot(): void
    {
        // 0. PSR-4 Autoloader for App\ namespace
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $baseDir = __DIR__ . '/../';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });

        // 1. Timezone
        date_default_timezone_set('Asia/Bangkok');

        // 2. Load .env
        self::loadEnv(__DIR__ . '/../../.env');

        // 3. Error reporting based on APP_DEBUG
        $debug = getenv('APP_DEBUG') === 'true';
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // 4. Start secure session
        Session::start();
    }

    /**
     * Minimal Fast .env Loader
     */
    private static function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');

                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

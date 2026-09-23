<?php
namespace App\Core;

/**
 * Fast Modular HTTP Router with Parameter Matching & Middleware
 */
class Router
{
    private static array $routes = [];

    public static function get(string $path, callable|array|string $handler, array $middleware = []): void
    {
        self::add('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, callable|array|string $handler, array $middleware = []): void
    {
        self::add('POST', $path, $handler, $middleware);
    }

    private static function add(string $method, string $path, callable|array|string $handler, array $middleware): void
    {
        self::$routes[] = [
            'method' => $method,
            'path' => rtrim($path, '/') ?: '/',
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public static function dispatch(string $uri, string $method): void
    {
        // Clean URI from query string and project base path
        $parsedUrl = parse_url($uri);
        $cleanPath = $parsedUrl['path'] ?? '/';
        
        // Strip out subfolder if hosted under /pcc or /hos
        if (str_starts_with($cleanPath, '/pcc')) {
            $cleanPath = substr($cleanPath, 4);
        } elseif (str_starts_with($cleanPath, '/hos')) {
            $cleanPath = substr($cleanPath, 4);
        }
        $cleanPath = rtrim($cleanPath, '/') ?: '/';

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert pattern like /patients/{pid} to regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $cleanPath, $matches)) {
                // Execute Middleware
                foreach ($route['middleware'] as $mw) {
                    self::runMiddleware($mw);
                }

                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Execute handler
                if (is_array($route['handler'])) {
                    [$class, $action] = $route['handler'];
                    $controller = new $class();
                    call_user_func_array([$controller, $action], $params);
                    return;
                } elseif (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $params);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (Request::isAjax() || str_starts_with($cleanPath, '/api/')) {
            Response::json(['status' => 'error', 'message' => 'Route not found: ' . $cleanPath], 404);
        } else {
            echo "<h1>404 Not Found</h1><p>ไม่พบหน้าที่เรียก: " . htmlspecialchars($cleanPath) . "</p>";
        }
    }

    private static function runMiddleware(string $mw): void
    {
        if ($mw === 'auth') {
            if (!Auth::check()) {
                Session::flash('error', 'กรุณาเข้าสู่ระบบก่อนใช้งาน');
                Response::redirect('/pcc/login');
            }
        } elseif ($mw === 'csrf') {
            if (Request::isPost() && !CSRF::validate()) {
                http_response_code(403);
                die('CSRF Token Validation Failed. กรุณารีเฟรชหน้าจอแล้วลองใหม่อีกครั้ง');
            }
        } elseif (str_starts_with($mw, 'role:')) {
            $role = substr($mw, 5);
            if (!Auth::hasRole($role)) {
                http_response_code(403);
                die('403 Forbidden: คุณไม่มีสิทธิ์ในบทบาทนี้ (' . htmlspecialchars($role) . ')');
            }
        } elseif (str_starts_with($mw, 'perm:')) {
            $perm = substr($mw, 5);
            if (!Auth::can($perm)) {
                http_response_code(403);
                die('403 Forbidden: คุณไม่มีสิทธิ์ในการดำเนินการนี้ (' . htmlspecialchars($perm) . ')');
            }
        } elseif (str_starts_with($mw, 'level:')) {
            $lvl = (int)substr($mw, 6);
            if (!Auth::canAccessLevel($lvl)) {
                http_response_code(403);
                die('403 Forbidden: คุณต้องมีระดับการเข้าถึงตั้งแต่ระดับ ' . $lvl . ' ขึ้นไปจึงจะสามารถเข้าใช้งานส่วนนี้ได้');
            }
        }
    }
}

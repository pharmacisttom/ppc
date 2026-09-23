<?php
namespace App\Core;

/**
 * View Rendering Engine with Layouts and Helpers
 */
class View
{
    /**
     * Render a view file inside the main layout (or standalone if layout is null)
     */
    public static function render(string $viewPath, array $data = [], ?string $layout = 'main'): void
    {
        extract($data);
        
        // Render view content into buffer
        ob_start();
        $viewFile = __DIR__ . '/../Views/' . ltrim($viewPath, '/') . '.php';
        if (!file_exists($viewFile)) {
            die("View file not found: {$viewFile}");
        }
        include $viewFile;
        $content = ob_get_clean();

        // Render inside layout
        if ($layout) {
            $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
                return;
            }
        }
        
        echo $content;
    }

    /**
     * Render view partially without layout (e.g. for AJAX or modals)
     */
    public static function partial(string $viewPath, array $data = []): string
    {
        extract($data);
        ob_start();
        $viewFile = __DIR__ . '/../Views/' . ltrim($viewPath, '/') . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        }
        return ob_get_clean();
    }
}

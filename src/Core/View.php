<?php

namespace App\Core;

class View
{
    private $viewsPath;
    private $data = [];

    public function __construct($viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?: __DIR__ . '/../Views/';
    }

    /**
     * Set data for the view
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Render a view
     */
    public function render($view, $data = [])
    {
        // Merge data
        $data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        $viewFile = $this->viewsPath . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View file not found: {$viewFile}");
        }

        // Get the content
        $content = ob_get_clean();

        // If layout is specified, render with layout
        if (isset($layout)) {
            return $this->renderWithLayout($layout, $content, $data);
        }

        return $content;
    }

    /**
     * Render view with layout
     */
    private function renderWithLayout($layout, $content, $data = [])
    {
        // Add content to data
        $data['content'] = $content;
        
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include the layout file
        $layoutFile = $this->viewsPath . 'layouts/' . $layout . '.php';
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            throw new \Exception("Layout file not found: {$layoutFile}");
        }

        // Return the content
        return ob_get_clean();
    }

    /**
     * Static method to quickly render a view
     */
    public static function make($view, $data = [])
    {
        $instance = new static();
        return $instance->render($view, $data);
    }

    /**
     * Display the rendered view
     */
    public function display($view, $data = [])
    {
        $content = $this->render($view, $data);
        
        // Check if layout is specified in the view
        if (isset($data['layout']) || (isset($GLOBALS['layout']) && $GLOBALS['layout'])) {
            $layout = $data['layout'] ?? $GLOBALS['layout'];
            $data['content'] = $content;
            echo $this->renderWithLayout($layout, $content, $data);
        } else {
            echo $content;
        }
    }

    /**
     * Render JSON response
     */
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Redirect to another URL
     */
    public static function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
}
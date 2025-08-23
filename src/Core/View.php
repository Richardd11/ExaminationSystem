<?php

namespace App\Core;

class View
{
    private $viewsPath;
    private $data = [];
    private $lastResolvedModule = null;

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
     * Resolve a view name to an existing file path, supporting modular structure
     */
    private function resolveViewFile($view)
    {
        $this->lastResolvedModule = null;
        $normalized = str_replace('.', '/', $view);
        $candidates = [];
        // Legacy/global Views directory
        $candidates[] = $this->viewsPath . $normalized . '.php';

        // Modular structure: src/{Module}/Views/{path}.php
        $parts = explode('.', $view);
        if (count($parts) >= 2) {
            $module = array_shift($parts);
            $moduleStudly = ucfirst($module);
            $relative = implode('/', $parts);
            $baseDir = dirname(__DIR__) . '/';
            $candidates[] = $baseDir . $moduleStudly . '/Views/' . $relative . '.php';
            $candidates[] = $baseDir . $module . '/Views/' . $relative . '.php';
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                // Remember module for layout resolution if applicable
                $relativeFromSrc = strpos($candidate, dirname(__DIR__) . '/') === 0
                    ? substr($candidate, strlen(dirname(__DIR__) . '/'))
                    : '';
                $segments = $relativeFromSrc !== '' ? explode('/', str_replace('\\', '/', $relativeFromSrc)) : [];
                if (!empty($segments)) {
                    $this->lastResolvedModule = $segments[0];
                }
                return $candidate;
            }
        }

        return null;
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
        $viewFile = $this->resolveViewFile($view);
        
        if ($viewFile !== null) {
            include $viewFile;
        } else {
            throw new \Exception("View file not found: " . ($this->viewsPath . str_replace('.', '/', $view) . '.php'));
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

        // Include the layout file (search global then module-specific)
        $candidates = [];
        $candidates[] = $this->viewsPath . 'layouts/' . $layout . '.php';
        if ($this->lastResolvedModule) {
            $baseDir = dirname(__DIR__) . '/';
            $candidates[] = $baseDir . $this->lastResolvedModule . '/Views/layouts/' . $layout . '.php';
            $candidates[] = $baseDir . strtolower($this->lastResolvedModule) . '/Views/layouts/' . $layout . '.php';
        }
        
        $layoutFile = null;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $layoutFile = $candidate;
                break;
            }
        }
        
        if ($layoutFile) {
            include $layoutFile;
        } else {
            throw new \Exception("Layout file not found: {$candidates[0]}");
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
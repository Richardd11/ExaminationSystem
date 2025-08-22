<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Router;

class RouterTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @group router
     * @group get
     */
    public function it_should_register_get_route()
    {
        // Arrange (Red Phase - Test First)
        $path = '/test';
        $callback = function() { return 'test response'; };

        // Act (Green Phase - Make it pass)
        $this->router->get($path, $callback);

        // Assert (Refactor Phase - Clean up)
        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($this->router);

        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey($path, $routes['GET']);
        $this->assertEquals($callback, $routes['GET'][$path]);
    }

    /**
     * @test
     * @group router
     * @group post
     */
    public function it_should_register_post_route()
    {
        // Arrange (Red Phase)
        $path = '/api/test';
        $callback = function() { return 'post response'; };

        // Act (Green Phase)
        $this->router->post($path, $callback);

        // Assert (Refactor Phase)
        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($this->router);

        $this->assertArrayHasKey('POST', $routes);
        $this->assertArrayHasKey($path, $routes['POST']);
        $this->assertEquals($callback, $routes['POST'][$path]);
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_dispatch_get_request_to_correct_route()
    {
        // Arrange (Red Phase)
        $path = '/test';
        $expectedResponse = 'test response';
        $callback = function() use ($expectedResponse) { return $expectedResponse; };

        $this->router->get($path, $callback);

        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $path;

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        $this->assertEquals($expectedResponse, $output);
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_dispatch_post_request_to_correct_route()
    {
        // Arrange (Red Phase)
        $path = '/api/test';
        $expectedResponse = 'post response';
        $callback = function() use ($expectedResponse) { return $expectedResponse; };

        $this->router->post($path, $callback);

        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = $path;

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        $this->assertEquals($expectedResponse, $output);
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_return_404_for_nonexistent_route()
    {
        // Arrange (Red Phase)
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent';

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        $this->assertStringContainsString('404', $output);
        $this->assertStringContainsString('Not Found', $output);
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_handle_root_path()
    {
        // Arrange (Red Phase)
        $path = '/';
        $expectedResponse = 'home response';
        $callback = function() use ($expectedResponse) { return $expectedResponse; };

        $this->router->get($path, $callback);

        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $path;

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        $this->assertEquals($expectedResponse, $output);
    }

    /** @test */
    public function it_should_handle_subdirectory_paths()
    {
        // Arrange (Red Phase)
        $path = '/login';
        $expectedResponse = 'login response';
        $callback = function() use ($expectedResponse) { return $expectedResponse; };

        $this->router->get($path, $callback);

        // Mock $_SERVER variables for subdirectory
        // The subdirectory should be consistent between REQUEST_URI and SCRIPT_NAME
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/subdirectory/login';
        $_SERVER['SCRIPT_NAME'] = '/subdirectory/index.php'; // This makes subdirectory = /subdirectory

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        // The router should strip /subdirectory from the path and match /login
        $this->assertEquals($expectedResponse, $output, 
            'Router should handle subdirectory paths by stripping the subdirectory part');
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_handle_parameterized_routes()
    {
        // Arrange (Red Phase)
        $path = '/users/{id}';
        $expectedResponse = 'user 123';
        $callback = function($id) use ($expectedResponse) { return "user $id"; };

        $this->router->get($path, $callback);

        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/users/123';

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        $this->assertEquals($expectedResponse, $output);
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_handle_multiple_parameters()
    {
        // Arrange (Red Phase)
        $path = '/users/{id}/posts/{postId}';
        $expectedResponse = 'user 123 post 456';
        $callback = function($id, $postId) use ($expectedResponse) { 
            return "user $id post $postId"; 
        };

        $this->router->get($path, $callback);

        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/users/123/posts/456';

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        $this->assertEquals($expectedResponse, $output);
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_handle_query_parameters()
    {
        // Arrange (Red Phase)
        $path = '/search';
        $expectedResponse = 'search response';
        $callback = function() use ($expectedResponse) { return $expectedResponse; };

        $this->router->get($path, $callback);

        // Mock $_SERVER variables with query string
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/search?q=test&page=1';

        // Act (Green Phase)
        ob_start();
        $this->router->dispatch();
        $output = ob_get_clean();

        // Assert (Refactor Phase)
        $this->assertEquals($expectedResponse, $output);
    }

    /**
     * @test
     * @group router
     * @group dispatch
     */
    public function it_should_handle_different_http_methods()
    {
        // Arrange (Red Phase)
        $getPath = '/api/users';
        $postPath = '/api/users';
        $putPath = '/api/users/123';
        $deletePath = '/api/users/123';

        $getCallback = function() { return 'GET response'; };
        $postCallback = function() { return 'POST response'; };
        $putCallback = function() { return 'PUT response'; };
        $deleteCallback = function() { return 'DELETE response'; };

        $this->router->get($getPath, $getCallback);
        $this->router->post($postPath, $postCallback);
        $this->router->put($putPath, $putCallback);
        $this->router->delete($deletePath, $deleteCallback);

        // Test GET
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $getPath;
        ob_start();
        $this->router->dispatch();
        $getOutput = ob_get_clean();
        $this->assertEquals('GET response', $getOutput);

        // Test POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = $postPath;
        ob_start();
        $this->router->dispatch();
        $postOutput = ob_get_clean();
        $this->assertEquals('POST response', $postOutput);

        // Test PUT
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = $putPath;
        ob_start();
        $this->router->dispatch();
        $putOutput = ob_get_clean();
        $this->assertEquals('PUT response', $putOutput);

        // Test DELETE
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = $deletePath;
        ob_start();
        $this->router->dispatch();
        $deleteOutput = ob_get_clean();
        $this->assertEquals('DELETE response', $deleteOutput);
    }
}
<?php

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Library\Container\Container;
use Library\Router\Router;

use function Library\run;

class ApplicationTest extends TestCase
{
    #[Test]
    public function runWithServerRequest()
    {
        $request = new ServerRequest(
            [],
            [],
            '/some/path',
            'POST',
            'php://input',
            [],
            [],
            [],
            null,
            '1.1'
        );
        $serviceManager = new Container( []);
        $router = new Router([]);
        
        run($serviceManager, $router, $request);

        $this->assertTrue(false);
    }
}
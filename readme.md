# Library

www/index.php
```php
<?php
chdir(dirname(__DIR__));
include __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Library\Container\Container;
use Library\Http\Response\ServerErrorResponse;
use Library\Http\ServerFilter\ContentTypeFilter;
use Library\Router\Router;

set_error_handler(function ($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$emitter = new SapiEmitter();

try {
    $request = ServerRequestFactory::fromGlobals(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES,
        new ContentTypeFilter(ContentTypeFilter::ARRAY),
    );

    $serviceManager = new Container( require './config/service.php');
    $router = new Router(require './config/router.php');

    $routeMatch = $router->match($request);
    if (!$routeMatch) {
        $emitter->emit(new EmptyResponse(406));
        exit;
    }
    $controllerName = $routeMatch->getValue();
    $attributes = $routeMatch->getAttributes();

    foreach($attributes as $key => $value) {
        $request = $request->withAttribute($key, $value);
    }

    /** @var \Psr\Http\Server\RequestHandlerInterface */
    $controller = $serviceManager->get($controllerName);
    $response = $controller->handle($request);

    $emitter->emit($response);
    exit;
}
catch (Throwable $throwable) {
    $response = new ServerErrorResponse($throwable);
    $emitter->emit($response);
    exit;
}

```

composer.json
```json
{
    "require": {
        "fizk/library": "0.0.1",
        "laminas/laminas-diactoros": "^3.3",
        "laminas/laminas-httphandlerrunner": "^2.10"
    }
}

```

config/router.php
```php
<?php

return [
    'index' => [
        '/^\/$/' => 'ClassName',
    ],
    'artist' => [],
];
```

config/service.php
```php

<?php

use Psr\Container\ContainerInterface;

return [
    'controllers' => [
        stdClass::class => function (ContainerInterface $container) {
            return new \stdClass;
        },
    ],

    'services' => [],

    'utils' => []
];
```
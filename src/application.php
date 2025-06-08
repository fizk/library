<?php

namespace Library;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Library\Http\Response\ServerErrorResponse;
use Library\Http\ServerFilter\ContentTypeFilter;
use Library\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

function run(ContainerInterface $serviceManager, RouterInterface $router, ?ServerRequestInterface $request = null): void
{
    $emitter = new SapiEmitter();

    try {
        $request = $request ?? ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES,
            new ContentTypeFilter(ContentTypeFilter::ARRAY),
        );

        $routeMatch = $router->match($request);
        if (!$routeMatch) {
            $emitter->emit(new EmptyResponse(406));
            exit;
        }
        $controllerName = $routeMatch->getValue();
        $attributes = $routeMatch->getAttributes();

        foreach ($attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        /** @var \Psr\Http\Server\RequestHandlerInterface */
        $controller = $serviceManager->get($controllerName);
        $response = $controller->handle($request);

        $emitter->emit($response);
        exit;
    } catch (\Throwable $throwable) {
        $response = new ServerErrorResponse($throwable);
        $emitter->emit($response);
        exit;
    }
}

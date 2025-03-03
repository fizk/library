# Controller

## MethodControllerTrait

This is a handy little `trait` that can be added to a controller class to make it aware of the different [HTTP verbs](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods).

The controller will then have a method per HTTP verb. If a method is not implemented, it will return a [405](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405) status code back.

```php
<?php

namespace App\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Library\Controller\MethodControllerTrait;

class IndexController implements RequestHandlerInterface
{
    use MethodControllerTrait;

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['message' => 'I am returning an item'], 200);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['message' => 'I just created an item'], 201);
    }
}
```

This way, one controller class can service one path or url and the different methods within it can service each HTTP verb. This just makes it more clear and avoids `switch` cases like this

```php
<?php

namespace App\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

class IndexController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        switch($request->getMethod()) {
            case 'get':
                // get item from service
                return new JsonResponse(['message' => 'I am returning an item'], 200);
            break;
            case 'post':
                // create item in service
                return new JsonResponse(['message' => 'I am returning an item'], 200);
            break;
            default:
                return new EmptyResponse(405);
            break;
        }
    }
}
```
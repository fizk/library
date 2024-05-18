<?php

namespace Library\Controller;

use Library\Http\Response\EmptyResponse;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

trait MethodControllerTrait
{
    protected function get(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    protected function post(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    protected function put(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    protected function delete(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    protected function head(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    protected function patch(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    protected function options(ServerRequestInterface $request): ResponseInterface
    {
        return new EmptyResponse(405);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return match (strtolower($request->getMethod())) {
            'get' => $this->get($request),
            'post' => $this->post($request),
            'put' => $this->put($request),
            'delete' => $this->delete($request),
            'head' => $this->head($request),
            'patch' => $this->patch($request),
            'options' => $this->options($request),
            default => new EmptyResponse(406)
        };
    }
}

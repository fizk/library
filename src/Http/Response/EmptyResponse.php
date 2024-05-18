<?php

namespace Library\Http\Response;

use Library\Http\Stream;
use Psr\Http\Message\ResponseInterface;

class EmptyResponse implements ResponseInterface
{
    use ResponseTrait;

    public function __construct(int $status = 204, array $headers = [])
    {
        $body = new Stream('php://temp', 'r');
        $this->setBody($body)
            ->setStatus($status);

        foreach ($headers as $key => $value) {
            $this->setAddedHeader($key, $value);
        }
    }
}

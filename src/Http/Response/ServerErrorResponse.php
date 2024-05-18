<?php

namespace Library\Http\Response;

use Library\Http\Stream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ServerErrorResponse implements ResponseInterface
{
    use ResponseTrait;

    public function __construct(Throwable $exception, int $status = 500)
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write(json_encode([
            'message' => $exception->getMessage(),
            'file' => $exception->getFile() . ':' . $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTrace(),
        ]));
        $stream->rewind();

        $this
            ->setStatus($status)
            ->setAddedHeader('content-type', 'application/json')
            ->setBody($stream);
    }
}

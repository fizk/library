<?php

namespace Library\Http\Response;

use Library\Form\Form;
use Library\Http\Stream;
use Psr\Http\Message\ResponseInterface;

class ClientErrorResponse implements ResponseInterface
{
    use ResponseTrait;

    public function __construct(Form $form, int $statusCode = 400)
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write(json_encode($form->getMessages()));
        $stream->rewind();

        $this
            ->setStatus($statusCode)
            ->setAddedHeader('content-type', 'application/json')
            ->setBody($stream);
    }
}

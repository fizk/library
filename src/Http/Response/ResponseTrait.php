<?php

namespace Library\Http\Response;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ResponseTrait
{
    private int $status = 200;

    private string $reasonPhrase = 'OK';

    private StreamInterface $body;

    private string $protocol = '1.1';

    private array $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated to 306 => '(Unused)'
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended (OBSOLETED)',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    private array $headers = [];

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $object = clone $this;
        $object->status = $code;
        if (empty($reasonPhrase)) {
            $object->reasonPhrase = array_key_exists($code, $this->phrases)
                ? $this->phrases[$code]
                : '';
        } else {
            $object->reasonPhrase = $reasonPhrase;
        }
        return $object;
    }

    private function setStatus(int $code, string $reasonPhrase = ''): self
    {
        $this->status = $code;
        if (empty($reasonPhrase)) {
            $this->reasonPhrase = array_key_exists($code, $this->phrases)
                ? $this->phrases[$code]
                : '';
        } else {
            $this->reasonPhrase = $reasonPhrase;
        }
        return $this;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $object = clone $this;
        $object->protocol = $version;
        return $object;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    public function getHeader(string $name): array
    {
        return  $this->hasHeader($name)
            ? $this->headers[$name]
            : [] ;
    }

    public function getHeaderLine(string $name): string
    {
        $headers = $this->getHeader($name);
        return implode(', ', $headers);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $object = clone $this;
        $object->headers[$name] = [$value];

        return $object;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $object = clone $this;
        if ($object->hasHeader($name)) {
            $object->headers[$name][] = $value;
        } else {
            $object->headers[$name] = [$value];
        }

        return $object;
    }

    private function setAddedHeader(string $name, $value): self
    {
        if ($this->hasHeader($name)) {
            $this->headers[$name][] = $value;
        } else {
            $this->headers[$name] = [$value];
        }

        return $this;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $object = clone $this;
        unset($object->headers[$name]);
        return $object;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $object = clone $this;
        $object->body = $body;
        return $object;
    }

    private function setBody(StreamInterface $body): self
    {
        $this->body = $body;
        return $this;
    }
}

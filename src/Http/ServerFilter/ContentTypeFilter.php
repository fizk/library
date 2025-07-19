<?php

namespace Library\Http\ServerFilter;

use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;

class ContentTypeFilter implements FilterServerRequestInterface
{
    public const ARRAY = 0;
    public const OBJECT = 1;

    public function __construct(private int $format = self::ARRAY)
    {
    }

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface
    {
        $method = strtoupper($request->getMethod());
        if ($method !== 'POST' && $method !== 'PUT' && $method !== 'PATCH') {
            return $request;
        }

        return match ($this->extractContentType($request)) {
            'application/json' => $request->withParsedBody(
                $this->deserializeJson($request)
            ),
            'application/x-www-form-urlencoded' => $request->withParsedBody(
                $this->deserializeUrlEncodedForm($request)
            ),
            'multipart/form-data' => $request->withParsedBody(
                $this->deserializeMultipartForm($request)
            ),
            null => $request->withParsedBody(
                $this->deserializeUrlEncodedForm($request)
            ),
            default => $request,
        };
    }

    private function extractContentType(ServerRequestInterface $request): ?string
    {
        $headers = $request->getHeader('content-type');
        if (count($headers) === 0) {
            return null;
        }

        preg_match('/^[a-z-]+\/[a-z-]+/', $headers[0], $match);

        return strtolower($match[0]);
    }

    private function deserializeJson(ServerRequestInterface $request): mixed
    {
        $bodyAsText = trim((string) $request->getBody());
        if (empty($bodyAsText)) {
            return null;
        }

        $json = json_decode($bodyAsText, $this->format === self::ARRAY);

        if ($json === null) {
            throw new UnexpectedValueException('JSON could not be parsed');
        }

        return $json;
    }

    private function deserializeUrlEncodedForm(ServerRequestInterface $request): mixed
    {
        $bodyAsText = trim((string) $request->getBody());
        if (empty($bodyAsText)) {
            return null;
        }

        $result = mb_parse_str($bodyAsText, $form);
        if ($result === false) {
            throw new UnexpectedValueException('Form could not be parsed');
        }

        return $this->format === self::ARRAY
            ? $form
            : (object) $form;
    }

    private function deserializeMultipartForm(ServerRequestInterface $request): mixed
    {
        try {
            if (strtolower($request->getMethod()) === 'post') {
                $input = $request->getParsedBody();
            } else {
                [$input] = request_parse_body();
            }

            return self::ARRAY === $this->format
                ? $input
                : (object) $input;
        } catch (Throwable $exception) {
            throw new UnexpectedValueException($exception->getMessage(), 0, $exception);
        }
    }
}

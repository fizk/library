<?php

namespace Library\Http\ServerFilter;

use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        $contentType = $request->getHeader('content-type')[0];
        $bodyAsText = trim((string) $request->getBody());

        if (empty($bodyAsText)) {
            return null;
        }

        if (!preg_match('/boundary=(.*)$/is', $contentType, $boundaries)) {
            return null;
        }

        $bodyParts = preg_split('/\\R?-+' . preg_quote($boundaries[1], '/') . '/s', $bodyAsText);

        return array_reduce($bodyParts, function ($previous, $bodyPart) {
            if (empty($bodyPart)) {
                return $previous;
            }

            @[$headers, $value] = preg_split('/\\R\\R/', $bodyPart, 2);
            $headers = $this->parseHeaders($headers);
            if (!isset($headers['content-disposition']['name'])) {
                return $previous;
            }
            $previous[$headers['content-disposition']['name']] = $value;
            return $previous;
        }, []);
    }

    /**
     *
     * @see https://github.com/notihnio/php-multipart-form-data-parser/blob/master/src/MultipartFormDataParser.php
     */
    private function parseHeaders(string $headerContent): array
    {
        $headers = [];
        $headerParts = preg_split('/\\R/s', $headerContent, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($headerParts as $headerPart) {
            if (!str_contains($headerPart, ':')) {
                continue;
            }
            [$headerName, $headerValue] = explode(':', $headerPart, 2);
            $headerName = strtolower(trim($headerName));
            $headerValue = trim($headerValue);
            if (!str_contains($headerValue, ';')) {
                $headers[$headerName] = $headerValue;
            } else {
                $headers[$headerName] = [];
                foreach (explode(';', $headerValue) as $part) {
                    $part = trim($part);
                    if (!str_contains($part, '=')) {
                        $headers[$headerName][] = $part;
                    } else {
                        [$name, $value] = explode('=', $part, 2);
                        $name = strtolower(trim($name));
                        $value = trim(trim($value), '"');
                        $headers[$headerName][$name] = $value;
                    }
                }
            }
        }
        return $headers;
    }
}

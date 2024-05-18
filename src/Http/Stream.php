<?php

namespace Library\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    private $resource;

    public function __construct(string $path, string $mode = 'rw')
    {
        $this->resource = fopen($path, $mode);
    }

    public function __destruct()
    {
        fclose($this->resource);
    }

    public function __toString(): string
    {
        return $this->getContents();
    }

    public function close(): void
    {
        if ($this->resource) {
            fclose($this->resource);
        }
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize(): ?int
    {
        if (!$this->resource) {
            return null;
        }
        return fstat($this->resource)['size'];
    }

    /**
     *
     * @throws \RuntimeException on error.
     */
    public function tell(): int
    {
        if (!$this->resource) {
            throw new RuntimeException('No stream available');
        }

        $tell = ftell($this->resource);

        if ($tell === false) {
            throw new RuntimeException();
        }
        return $tell;
    }

    public function eof(): bool
    {
        if (!$this->resource) {
            return true;
        }

        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        if (!$this->resource) {
            return false;
        }

        return stream_get_meta_data($this->resource)['seekable'];
    }

    /**
     *
     * @throws \RuntimeException on failure.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Can not seek');
        }

        $result = fseek($this->resource, $offset, $whence);

        if ($result < 0) {
            throw new RuntimeException('Error seeking stream');
        }
    }

    /**
     *
     * @throws \RuntimeException on failure.
     */
    public function rewind(): void
    {
        if (!$this->resource) {
            return;
        }
        $result = rewind($this->resource);

        if ($result === false) {
            throw new RuntimeException('Could not rewind');
        }
    }

    public function isWritable(): bool
    {
        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        return strstr($mode, 'x')
            || strstr($mode, 'w')
            || strstr($mode, 'c')
            || strstr($mode, 'a')
            || strstr($mode, '+');
    }

    /**
     *
     * @throws \RuntimeException on failure.
     */
    public function write(string $string): int
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Can not write to stream');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new RuntimeException('Did not write to stream');
        }

        return $result;
    }

    public function isReadable(): bool
    {
        if (!$this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return strstr($mode, 'r') || strstr($mode, '+');
    }

    /**
     *
     * @throws \RuntimeException if an error occurs.
     */
    public function read(int $length): string
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource provided');
        }

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new RuntimeException('Can not read');
        }

        return $result;
    }

    /**
     *
     * @throws \RuntimeException if unable to read.
     * @throws \RuntimeException if error occurs while reading.
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Can not read from resource');
        }

        $result = stream_get_contents($this->resource);

        if ($result === false) {
            throw new RuntimeException('Error getting resource content');
        }

        return $result;
    }

    public function getMetadata(?string $key = null)
    {
        $meta = stream_get_meta_data($this->resource);
        if ($key) {
            return array_key_exists($key, $meta)
                ? $meta[$key]
                : null;
        }
        return $meta;
    }
}

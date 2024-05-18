<?php

namespace Library\Http\ServerFilter;

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use UnexpectedValueException;

class ContentTypeFilterTest extends TestCase
{
    public function testGetRequestReturnsNull()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('{"name": "some name"}');

        $request = (new ServerRequest([], [], '/some/path', 'GET', $stream, [
            'content-type' => 'application/json'
        ]));

        $contentFilter = new ContentTypeFilter();

        $response = $contentFilter($request);

        $expected = null;
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsValidJSONAsDefault()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('{"name": "some name"}');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/json'
        ]));

        $contentFilter = new ContentTypeFilter();

        $response = $contentFilter($request);

        $expected = ["name" => "some name"];
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsValidJSONAsAnObject()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('{"name": "some name"}');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/json'
        ]));

        $contentFilter = new ContentTypeFilter(ContentTypeFilter::OBJECT);

        $response = $contentFilter($request);

        $expected = json_decode('{"name": "some name"}');
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsValidJSONAsAnArray()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('{"name": "some name"}');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/json'
        ]));

        $contentFilter = new ContentTypeFilter(ContentTypeFilter::ARRAY);

        $response = $contentFilter($request);

        $expected = ["name" => "some name"];
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsValidFormAsDefault()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('name=some name');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/x-www-form-urlencoded'
        ]));

        $contentFilter = new ContentTypeFilter();

        $response = $contentFilter($request);

        $expected = ["name" => "some name"];
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsValidFormAsAnArray()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('name=some name');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/x-www-form-urlencoded'
        ]));

        $contentFilter = new ContentTypeFilter(ContentTypeFilter::ARRAY);

        $response = $contentFilter($request);

        $expected = ["name" => "some name"];
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsValidFormAsAnObject()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('name=some name');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/x-www-form-urlencoded'
        ]));

        $contentFilter = new ContentTypeFilter(ContentTypeFilter::OBJECT);

        $response = $contentFilter($request);

        $expected = (object) ["name" => "some name"];
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testExpectsFormDataIfNoContentType()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('name=some name');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, []));

        $contentFilter = new ContentTypeFilter();

        $response = $contentFilter($request);

        $expected = ["name" => "some name"];
        $actual = $response->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testUnknownContentTypeDoesNothing()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('name=some name');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'text/plain'
        ]));

        $contentFilter = new ContentTypeFilter();

        $response = $contentFilter($request);

        $expected = null;
        $actual = $response->getParsedBody();
        $s = $response->getBody()->__toString();

        $this->assertEquals($expected, $actual);
    }

    public function testInvalidJson()
    {
        $this->expectException(UnexpectedValueException::class);

        $stream = new Stream('php://temp', 'rw');
        $stream->write('{"invalid": "json');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/json'
        ]));

        $contentFilter = new ContentTypeFilter();

        $contentFilter($request);
    }

    public function testInvalidForm()
    {
        $this->markTestSkipped('I can not get mb_parse_str to produce an exception');
        $this->expectException(UnexpectedValueException::class);

        $stream = new Stream('php://temp', 'rw');
        $stream->write('&"key[]]?=value');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, []));

        $contentFilter = new ContentTypeFilter();

        $contentFilter($request);
    }

    public function testJsonEmptyString()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/json'
        ]));

        $contentFilter = new ContentTypeFilter();

        $expected = null;
        $actual = $contentFilter($request)->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testFormEmptyString()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write('');

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'application/x-www-form-urlencoded'
        ]));

        $contentFilter = new ContentTypeFilter();

        $expected = null;
        $actual = $contentFilter($request)->getParsedBody();

        $this->assertEquals($expected, $actual);
    }

    public function testMultipartFormData()
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write(
            '' . PHP_EOL .
            '------------------------------83ff53821b7c' . PHP_EOL .
            'Content-Disposition: form-data; name="foo"' . PHP_EOL .
            '' . PHP_EOL .
            'bar' . PHP_EOL .
            '------------------------------83ff53821b7c' . PHP_EOL .
            'Content-Transfer-Encoding: base64' . PHP_EOL .
            '' . PHP_EOL .
            'YmFzZTY0' . PHP_EOL .
            '------------------------------83ff53821b7c' . PHP_EOL .
            'Content-Disposition: form-data; name="upload"; filename="text.txt"' . PHP_EOL .
            'Content-Type: text/plain' . PHP_EOL .
            '' . PHP_EOL .
            'File content' . PHP_EOL .
            '------------------------------83ff53821b7c--' . PHP_EOL
        );

        $request = (new ServerRequest([], [], '/some/path', 'PUT', $stream, [
            'content-type' => 'multipart/form-data; boundary=----------------------------83ff53821b7c'
        ]));

        $contentFilter = new ContentTypeFilter();
        $request = $contentFilter($request);

        $expected = [
            'foo' => 'bar',
            'upload' => 'File content',
        ];
        $actual = $request->getParsedBody();

        $this->assertEquals($expected, $actual);
    }
}

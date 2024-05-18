<?php

namespace Library\Http\Response;

use Exception;
use Library\Http\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ServerErrorResponseTest extends TestCase
{
    public function testDefaultStatusCode()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN

        // THEN
        $actual = $response->getStatusCode();
        $expected = 500;
        $this->assertEquals($expected, $actual);
    }

    public function testChangeStatusCode()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception(), 501);

        // WHEN

        // THEN
        $actual = $response->getStatusCode();
        $expected = 501;
        $this->assertEquals($expected, $actual);
    }

    public function testWithStatusCode()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withStatus(400);

        // THEN
        $actual = $response->getStatusCode();
        $expected = 400;
        $this->assertEquals($expected, $actual);
    }

    public function testWithUndefinedStatusCode()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withStatus(900);

        // THEN
        $actual = $response->getStatusCode();
        $expected = 900;
        $this->assertEquals($expected, $actual);

        // THEN
        $actual = $response->getReasonPhrase();
        $expected = '';
        $this->assertEquals($expected, $actual);
    }

    public function testWithStandardReasonPhrase()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withStatus(405);

        // THEN
        $actual = $response->getReasonPhrase();
        $expected = 'Method Not Allowed';
        $this->assertEquals($expected, $actual);
    }

    public function testWithCustomReasonPhrase()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withStatus(400, 'some text');

        // THEN
        $actual = $response->getReasonPhrase();
        $expected = 'some text';
        $this->assertEquals($expected, $actual);
    }

    public function testProtocolVersion()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $actual = $response->getProtocolVersion();
        $expected = '1.1';

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testCustomProtocolVersion()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withProtocolVersion('2');
        $actual = $response->getProtocolVersion();
        $expected = '2';

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testDefaultHeaders()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN

        // THEN
        $actual = $response->getHeaders();
        $expected = [
            'content-type' => ['application/json']
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testOverrideHeaders()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withHeader('content-type', 'text/plain');

        // THEN
        $actual = $response->getHeaders();
        $expected = [
            'content-type' => ['text/plain']
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testAddHeaders()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withAddedHeader('content-type', 'text/plain');

        // THEN
        $actual = $response->getHeaders();
        $expected = [
            'content-type' => ['application/json', 'text/plain']
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testAddNewHeaders()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withAddedHeader('x-forwarded-by', 'value');

        // THEN
        $actual = $response->getHeaders();
        $expected = [
            'content-type' => ['application/json'],
            'x-forwarded-by' => ['value'],
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testHasHeader()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withHeader('x-forwarded-by', 'host');

        // THEN
        $actual = $response->hasHeader('x-forwarded-by');
        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    public function testHasNotHeader()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN

        // THEN
        $actual = $response->hasHeader('x-no_header');
        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    public function testNewHeader()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withHeader('x-forwarded-by', 'host');

        // THEN
        $actual = $response->getHeaders();
        $expected = [
            'content-type' => ['application/json'],
            'x-forwarded-by' => ['host']
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGetHeader()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN

        // THEN
        $actual = $response->getHeader('content-type');
        $expected = ['application/json'];
        $this->assertEquals($expected, $actual);
    }

    public function testGetUndefinedHeader()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN

        // THEN
        $actual = $response->getHeader('x-no_header');
        $expected = [];
        $this->assertEquals($expected, $actual);
    }

    public function testGetHeaderLine()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withAddedHeader('content-type', 'text/plain');

        // THEN
        $actual = $response->getHeaderLine('content-type');
        $expected = 'application/json, text/plain';
        $this->assertEquals($expected, $actual);
    }

    public function testWithoutHeader()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN
        $response = $response->withoutHeader('content-type');

        // THEN
        $actual = $response->getHeaders();
        $expected = [];
        $this->assertEquals($expected, $actual);
    }

    public function testGetBody()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN

        // THEN
        $actual = $response->getBody();
        $expected = StreamInterface::class;
        $this->assertInstanceOf($expected, $actual);
    }

    public function testGetContentIsJson()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());

        // WHEN

        // THEN
        $actual = $response->getBody()->getContents();
        $this->assertJson($actual);
    }

    public function testWithBody()
    {
        // GIVEN
        $response = new ServerErrorResponse(new Exception());
        $stream = new Stream('php://memory');
        $stream->write('I am new content');
        $stream->rewind();

        // WHEN
        $response = $response->withBody($stream);

        // THEN
        $expected = 'I am new content';
        $actual = $response->getBody()->getContents();
        $this->assertEquals($expected, $actual);
    }
}

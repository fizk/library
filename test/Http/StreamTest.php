<?php

namespace Library\Http;

use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testSizeEmpty()
    {
        // GIVEN
        $stream = new Stream('php://temp');

        // THEN
        $actual = $stream->getSize();
        $expected = 0;
        $this->assertEquals($expected, $actual);
    }

    public function testSizeDetached()
    {
        // GIVEN
        $stream = new Stream('php://temp');

        // THEN
        $expected = null;
        $actual = $stream->getSize();
        $this->assertEquals($expected, $actual);
    }

    public function testSizeWithValue()
    {
        // GIVEN
        $content = 'This is the content';
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write($content);

        // THEN
        $expected = strlen($content);
        $actual = $stream->getSize();
        $this->assertEquals($expected, $actual);
    }

    public function testTell()
    {
        // GIVEN
        $streamContent = 'this should be in the stream';
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write($streamContent);

        // THEN
        $expected = strlen('this should be in the stream');
        $actual = $stream->tell();
        $this->assertEquals($expected, $actual);

        // WHEN
        $stream->rewind();

        // THEN
        $expected = 0;
        $actual = $stream->tell();
        $this->assertEquals($expected, $actual);
    }

    public function testEOF()
    {
        // GIVEN
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write('write content that is less than 1024');
        $stream->read(1024);

        // THEN
        $expected = true;
        $actual = $stream->eof();
        $this->assertEquals($expected, $actual);
    }

    public function testEOFNothingHasBeenRead()
    {
        // GIVEN
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write('write content that is less than 1024');

        // THEN
        $expected = false;
        $actual = $stream->eof();
        $this->assertEquals($expected, $actual);
    }

    public function testIsSeekable()
    {
        // GIVEN
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write('this should be in the stream');

        // THEN
        $expected = true;
        $actual = $stream->isSeekable();
        $this->assertEquals($expected, $actual);
    }

    public function testSeek()
    {
        // THEN
        $this->expectNotToPerformAssertions();

        // GIVEN
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write('this should be in the stream');
        $stream->seek(10);
    }

    public function testRewind()
    {
        // THEN
        $this->expectNotToPerformAssertions();

        // GIVEN
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write('this should be in the stream');
        $stream->rewind();
    }

    public function testIsWritable()
    {
        // GIVEN
        $stream = new Stream('php://temp');

        // THEN
        $expected = true;
        $actual = $stream->isWritable();
        $this->assertEquals($expected, $actual);
    }

    public function testIsNotWritable()
    {
        // GIVEN
        $stream = new Stream('php://temp', 'r');

        // THEN
        $expected = false;
        $actual = $stream->isWritable();
        $this->assertEquals($expected, $actual);
    }

    public function testWrite()
    {
        // GIVEN
        $content = 'this should be in the stream';
        $stream = new Stream('php://temp');

        // WHEN
        $size = $stream->write($content);

        // THEN
        $expected = strlen($content);
        $actual = $size;
        $this->assertEquals($expected, $actual);
    }

    public function testWriteException()
    {
        // THEN
        $this->expectException(\RuntimeException::class);

        // GIVEN
        $content = 'this should be in the stream';
        $stream = new Stream('php://temp', 'r');

        // WHEN
        $stream->write($content);
    }

    public function testIsReadable()
    {
        // GIVEN
        $content = 'this should be in the stream';
        $stream = new Stream('php://temp', 'r');

        // THEN
        $expected = true;
        $actual = $stream->isReadable($content);
        $this->assertEquals($expected, $actual);
    }

    public function testIsNotReadable()
    {
        // GIVEN
        $stream = new Stream('php://stdin', 'w');

        // THEN
        $expected = false;
        $actual = $stream->isReadable();
        $this->assertEquals($expected, $actual);
    }

    public function testRead()
    {
        // GIVEN
        $content = 'This is content';
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write($content);
        $stream->rewind();

        // THEN
        $expected = $content;
        $actual = $stream->read(1024);
        $this->assertEquals($expected, $actual);
    }

    public function testCanNotRead()
    {
        $this->markTestSkipped();
        // THEN
        $this->expectException(\RuntimeException::class);

        // GIVEN
        $content = 'This is content';
        $stream = new Stream('php://memory', 'w');

        // WHEN
        $stream->write($content);
        $stream->rewind();
        $stream->read(1024);
    }

    public function testGetContent()
    {
        // GIVEN
        $content = 'This is content';
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write($content);
        $stream->rewind();

        // THEN
        $expected = $content;
        $actual = $stream->getContents();
        $this->assertEquals($expected, $actual);
    }

    public function testToString()
    {
        // GIVEN
        $content = 'This is content';
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write($content);
        $stream->rewind();

        // THEN
        $expected = $content;
        $actual = (string) $stream;
        $this->assertEquals($expected, $actual);
    }

    public function testGetMetadata()
    {
        // GIVEN
        $content = 'This is content';
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write($content);
        $stream->rewind();

        // THEN
        $actual = $stream->getMetadata();
        $this->assertIsArray($actual);
    }

    public function testGetMetadataMode()
    {
        // GIVEN
        $content = 'This is content';
        $stream = new Stream('php://temp');

        // WHEN
        $stream->write($content);
        $stream->rewind();

        // THEN
        $actual = $stream->getMetadata('mode');
        $this->assertIsString($actual);
    }
}

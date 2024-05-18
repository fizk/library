<?php

namespace Library\Router;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testMatch()
    {
        // GIVEN
        $request = (new ServerRequest([], [], '/'));
        $router = new Router([
            'group' => [
                '/^\/$/' => 'value'
            ]
        ]);

        // WHEN
        $expected = new RouterMatch('value', [], '/^\/$/');
        $actual = $router->match($request);

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testMatchAdd()
    {
        // GIVEN
        $request = (new ServerRequest([], [], '/'));
        $router = (new Router())
            ->add('/^\/$/', 'value')
            ->add('/^\/item\/(?<id>[0-9]+)$/', 'no-value')
        ;

        // WHEN
        $expected = new RouterMatch('value', [], '/^\/$/');
        $actual = $router->match($request);

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testMatchRemove()
    {
        // GIVEN
        $request = (new ServerRequest([], [], '/'));
        $router = new Router([
            'group' => [
                '/^\/$/' => 'value'
            ]
        ]);

        // WHEN
        $router->remove('/^\/$/');
        $expected = null;
        $actual = $router->match($request);

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testUsingBothConfigAndAdd()
    {
        // GIVEN
        $request = (new ServerRequest([], [], '/item/1'));
        $router = (new Router([
            'group' => [
                '/^\/$/' => 'no-value'
            ]
        ]))->add('/^\/item\/(?<id>[0-9]+)$/', 'value');

        // WHEN
        $expected = new RouterMatch(
            'value',
            ['1', 'id' => '1'],
            '/^\/item\/(?<id>[0-9]+)$/'
        );
        $actual = $router->match($request);

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testNoMatch()
    {
        // GIVEN
        $request = (new ServerRequest([], [], '/item'));
        $router = new Router([
            'group' => [
                '/^\/$/' => 'value',
            ]
        ]);

        // WHEN
        $expected = null;
        $actual = $router->match($request);

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testAttribute()
    {
        // GIVEN
        $request = (new ServerRequest([], [], '/item/2'));
        $router = new Router([
            'group' => [
                '/^\/$/' => 'no-value',
                '/^\/item\/(?<id>[0-9]+)$/' => 'value'
            ]
        ]);

        // WHEN
        $expected = new RouterMatch(
            'value',
            ['2', 'id' => '2'],
            '/^\/item\/(?<id>[0-9]+)$/'
        );
        $actual = $router->match($request);

        // THEN
        $this->assertEquals($expected, $actual);
    }

    public function testAttributes()
    {
        // GIVEN
        $request = (new ServerRequest([], [], '/collection/1/item/2'));
        $router = new Router([
            'group' => [
                '/^\/$/' => 'no-value',
                '/^\/collection\/(?<a>[0-9]+)\/item\/(?<b>[0-9]+)$/' => 'value'
            ]
        ]);

        // WHEN
        $expected = new RouterMatch(
            'value',
            ['1', '2', 'a' => '1', 'b' => '2'],
            '/^\/collection\/(?<a>[0-9]+)\/item\/(?<b>[0-9]+)$/'
        );
        $actual = $router->match($request);

        // THEN
        $this->assertEquals($expected, $actual);
    }
}

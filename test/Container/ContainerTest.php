<?php

namespace Library\Container;

use Library\Container\Exception\AlreadyDefinedException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Library\Container\Exception\NotFoundException;
use Library\Container\Exception\InvalidDefinitionException;
use stdClass;

class ContainerTest extends TestCase
{
    public function testHasIsFalse()
    {
        // GIVEN
        $container = new Container([]);

        // WHEN
        $actual = $container->has('does not exist');

        // THEN
        $this->assertFalse($actual);
    }

    public function testHasIsTrue()
    {
        // GIVEN
        $container = new Container([
            'one' => [
                'does exist' => function () {
                },
            ],
        ]);

        // WHEN
        $actual = $container->has('does exist');

        // THEN
        $this->assertTrue($actual);
    }

    public function testGetValidDependency()
    {
        // GIVEN
        $container = new Container([
            'service' => [
                stdClass::class => fn () => new stdClass()
            ],
        ]);

        // WHEN
        $actual = $container->get(stdClass::class);

        // THEN
        $this->assertInstanceOf(stdClass::class, $actual);
    }

    public function testGetFromCache()
    {
        // GIVEN
        $container = new Container([
            'service' => [
                stdClass::class => fn () => new stdClass()
            ],
        ]);

        // WHEN
        $actual1 = $container->get(stdClass::class);
        $actual2 = $container->get(stdClass::class);

        // THEN
        $this->assertSame($actual1, $actual2);
    }

    public function testGetNotFoundException()
    {
        // GIVEN
        $container = new Container([]);

        // WHEN
        $this->expectException(NotFoundException::class);

        // THEN
        $container->get(stdClass::class);
    }

    public function testGetDefinitionIsNotCallable()
    {
        // GIVEN
        $container = new Container([
            'service' => [
                stdClass::class => 'i am not callable'
            ],
        ]);

        // THEN
        $this->expectException(InvalidDefinitionException::class);

        // WHEN
        $container->get(stdClass::class);
    }

    public function testRecurtion()
    {
        // GIVEN
        $container = new Container([
            'service' => [
                'does exist' => function (ContainerInterface $container) {
                    $service1 = new stdClass();
                    $service1->instance = $container->get('another');
                    return $service1;
                },
            ],
            'utilities' => [
                'another' => function () {
                    return new stdClass();
                }
            ],
        ]);

        // WHEN
        $parentDependency = $container->get('does exist');
        $childDependency = $container->get('another');

        // THEN
        $this->assertInstanceOf(stdClass::class, $parentDependency);
        $this->assertInstanceOf(stdClass::class, $parentDependency->instance);
        $this->assertSame($childDependency, $parentDependency->instance);
    }

    public function testNestedDependenciesAreCached()
    {
        // GIVEN
        $container = new Container([
            'service' => [
                'does exist' => function (ContainerInterface $container) {
                    $service1 = new stdClass();
                    $service1->instance = $container->get('another');
                    return $service1;
                },
            ],
            'utilities' => [
                'another' => function () {
                    return new stdClass();
                }
            ],
        ]);

        // WHEN
        $dependencyOne = $container->get('does exist');
        $dependencyTwo = $container->get('does exist');
        $subDependency = $container->get('another');

        // THEN
        $this->assertSame($dependencyOne, $dependencyTwo);
        $this->assertSame($dependencyOne->instance, $dependencyTwo->instance);

        $this->assertSame($dependencyOne->instance, $subDependency);
        $this->assertSame($dependencyTwo->instance, $subDependency);
    }

    public function testAddFound()
    {
        // GIVEN
        $container = new Container();

        // WHEN
        $container->add('key', fn () => null);

        // THEN
        $this->assertTrue($container->has('key'));
    }

    public function testAddAlreadyDefined()
    {
        // THEN
        $this->expectException(AlreadyDefinedException::class);

        // GIVEN
        $container = new Container([
            'group' => [
                'key' => fn () => true,
            ]
        ]);

        // WHEN
        $container->add('key', fn () => null);
    }

    public function testRemoveNotInCache()
    {
        // GIVEN
        $container = new Container();

        // WHEN
        $container->add('key', fn () => null);
        $container->get('key');

        // THEN
        $this->assertTrue($container->has('key'));

        // WHEN
        $container->remove('key');

        // THEN
        $this->assertFalse($container->has('key'));
    }

    public function testRemoveNotListed()
    {
        // GIVEN
        $container = new Container();

        // WHEN
        $container->add('key', fn () => null);
        $container->remove('key', fn () => null);
        $container->remove('not a key', fn () => null);

        // THEN
        $this->assertFalse($container->has('key'));
        $this->assertFalse($container->has('not a key'));
    }

    public function testRemoveButNotDependencies()
    {
        // GIVEN
        $container = new Container();

        // WHEN
        $container->add('key', function (ContainerInterface $container) {
            $container->get('sub-key');
            return new stdClass();
        })->add('sub-key', function () {
            return new stdClass();
        });
        $container->get('key');

        // THEN
        $this->assertTrue($container->has('key'));
        $this->assertTrue($container->has('sub-key'));

        // WHEN
        $container->remove('key');

        // THEN
        $this->assertFalse($container->has('key'));
        $this->assertTrue($container->has('sub-key'));
    }

    public function testCallableObject()
    {
        $definition = new class {
            public function __invoke(ContainerInterface $container)
            {
                return $container->get('dependency');
            }
        };

        // GIVEN
        $container = new Container([
            'group' => [
                'key' => $definition,
                'dependency' => fn () => new stdClass()
            ]
        ]);

        $this->assertInstanceOf(stdClass::class, $container->get('key'));
    }

    public function testOverride()
    {
        // GIVEN
        $container = new Container([
            'group' => [
                'key' => fn () => 'primary definition'
            ]
        ]);

        // WHEN
        $container->override('key', fn () => 'secondary definition');

        // THEN
        $expected = 'secondary definition';
        $actual = $container->get('key');
        $this->assertEquals($expected, $actual);
    }

    public function testOverrideException()
    {
        // THEN

        $this->expectException(NotFoundException::class);
        // GIVEN
        $container = new Container([
            'group' => [
                'key' => fn () => 'primary definition'
            ]
        ]);

        // WHEN
        $container->override('new-key', fn () => 'secondary definition');
    }
}

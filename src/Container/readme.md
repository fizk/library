# Container
Simple `ServiceManager / Dependency Injector / Container` that implements the [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/).

```php
namespace Psr\Container;

interface ContainerInterface
{
    public function get($id): mixed;

    public function has($id): bool;
}
```

### How to use.
The constructor is expecting a configuration array that lists all services available. The structure is as follow

```php
$config = [
    'group-1' => [
        'key-to-query-for1' => function (ContainerInterface $container) {
            return new Service
        },
        'key-to-query-for2' => function (ContainerInterface $container) {
            return new Service
        },
    ],
    'group-2' => [
        'key-to-query-for3' => function (ContainerInterface $container) {
            return new Service
        },
        'key-to-query-for4' => function (ContainerInterface $container) {
            return new Service
        },
    ],
];
```

The `group-*` names are for convenience. They are there to make it simpler to group different type of services like: controllers, services/repositories, utility objects etc... They can be _what-ever_ string. They will be discarded once the `Container` object is created, therefor; two groups can not contain the same `key`, the first one will be overwritten with the second one.

Each `key` needs to return a [`callable`](https://www.php.net/manual/en/language.types.callable.php).

When the `callable` is called, it will be passed a reference to the same Container instance (`$this`) allowing each callable to query other services within the configuration file.


```php
$config = [
    'database-services' => [
        ItemService::class => function (ContainerInterface $container) {
            return (new ItemService())
                ->setPdo($container->get(PDO::class))
        },
    ],
    'utility' => [
        PDO::class => function (ContainerInterface $container) {
            return new PDO();
        },
    ],
];


$container = new Container($config);

$itemService = $container->get(ItemService::class);
```

## Additional functionality
This implementation of the [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/) adds a few methods which are not supported by the standard.

### add(string $id, callable $definition)
If you want to have a finer control over how the Container gets configured, you can use the `add` method.

```php
$container = (new Container())
    ->add('key-one', function(ContainerInterface $container) {
        return (new Service())
            ->addDependency($container->get('key-two'))
        ;
    })
    ->add('key-two', function(ContainerInterface $container) {
        return new \stdClass();
    })
;

assert($container->has('key-one'));
assert($container->has('key-two'));
```

`add()` will throw an exception if the key is already defined.

### remove(string $id)
You can remove an already defined definition.

```php
$container = (new Container())
    ->add('key-one', function(ContainerInterface $container) {
        return (new Service())
            ->addDependency($container->get('key-two'))
        ;
    })
    ->add('key-two', function(ContainerInterface $container) {
        return new \stdClass();
    })
;

$container->remove('key-one');

assert( ! $container->has('key-one'));
assert(   $container->has('key-two'));
```

### override(string $id, callable $definition)
While the `add()` will throw an exception if you try to define an already defined key, the `override` will do the opposite: it will throw an exception if you try to define a definition of a key that does not exist.

This is useful for testing when you want to define a _mock definition_ and you want to make sure that the original configuration already has this key defined.

```php
$container = (new Container())
    ->add('key-one', function(ContainerInterface $container) {
        return (new Service())
            ->addDependency($container->get('key-two'))
        ;
    })
    ->add('key-two', function(ContainerInterface $container) {
        return new \stdClass();
    })
;

$container->override('key-one', fn () => 'new definition');
$container->override('new-key', fn () => 'will throw exception');
```

## Inner workings.
The Container is lazy-loaded, that is: it will not run the definition function until the `$container->get('key')` is called. Once that happens, the container will keep a cached version of the `callable`'s return value.

Calling `::get()` on the same key multiple times will return the same instance of the service object/value.

```php
$container = new Container([
    'group' => [
        'key' => fn () => new \stdClass()
    ]
]);

$service1 = $container->get('key');
$service2 = $container->get('key');

assert($service1 === $service2);
```

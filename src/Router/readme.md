## Router

A very simple class that matches `ServerRequest` object against a list of RegExp, returning a value attached to that Regular Expression.

```php
$request = new ServerRequest([], [], '/items/1');

$routerConfig = [
    'group-1' => [
        '/^\/$/' => IndexController::class,
        '/^\/items$/' => ItemsController::class,
        '/^\/items\/(?<id>[0-9]+)$/' => ItemController::class,
    ]
];

$router = new Router($routerConfig);

$match = $router->match($request);

$match->getValue(); // returns: ItemController::class
$match->getAttributes(); // returns: ['1', 'id' => '1]
$match->getPattern(); // returns: '/^\/items\/(?<id>[0-9]+)$/'
```

## How it works.
This is just a fancy wrapper around a `forEach` loop that checks the key of an array (which is a regular expression) against the **Url Path** provided through the `ServerRequest`. If there is a match, it will return the value that this array-key points to, wrapped in a `RouterMatch` object. It there is no match; a `null` will be returned.

## What it returns.
If there is a match, a `RouterMatch` object will be returned.

```php
class RouterMatch
{
    public function getValue(): string;

    public function getAttributes(): array;

    public function getPattern(): string;
}
```

- **getValue** returns the value of the array
- **getPattern** returns the key of the array
- **getAttributes** returns the `$matches` of the `preg_match(string $pattern, string $subject, array &$matches = null)` value.

## How to configure.
The simplest way to configure the router is to provide an array to the constructor

```php
$config = [
    'group-1' => [
        '/<regexp>/' => 'value 1',
        '/<regexp>/' => 'value 2',
    ],
    'group-2' => [
        '/<regexp>/' => 'value 3',
        '/<regexp>/' => 'value 4',
    ],
];

$router = new Router($config);
```

The `group-*` keys are just for convenience, just so paths can be grouped. They will be removed once the `Router` object is configured. This means that two groups can not share a key; the second one will override the first one. Keys can be any string.

Another way is to use the `public function add(string $regex, string $value): self` method:

```php
$router = (new Router())
    ->add('/<regexp>/', 'value 1')
    ->add('/<regexp>/', 'value 2')
    ->add('/<regexp>/', 'value 3')
    ->add('/<regexp>/', 'value 4')
```

## An example
A concrete example used in combination with [PSR-15: HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/), [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) and [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/) could look something like this

```php
$request = new Request(); // PSR-7: HTTP message
$container = new Container(); // PSR-11: Container
$router = new Router(require './router.php');

// Check if there was a match
if (($match = $router->match($request))) {
    // Get controller from service-manager/container
    $controller = $container->get($match->getValue()); // $controller is a PSR-15: HTTP Server Request Handler object

    // Add attributes from URL into the Request object
    foreach($match->getAttributes() as $key => $value) {
        $request = $request->withAttribute($key, $value);
    }

    // Run the controller and get a Response object
    $response = $controller->handle($request);
} else {
    $response = null
}

// Handle the Response object.

```

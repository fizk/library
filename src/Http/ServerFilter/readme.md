## Server Filters.

The [Laminas\Diactoros\RequestFactory::fromGlobals()](https://docs.laminas.dev/laminas-diactoros/v3/factories/) allows for injection of a class to futher [filter](https://docs.laminas.dev/laminas-diactoros/v3/server-request-filters/) HTTP requests. This is a great place to fetch data for **PUT** and **PATCH** requests as well as format/parse different `content-types`s.

### ContentTypeFilter.
The `ContentTypeFilter` class reads data from `php://input` and based on the **content-types**, it will format/deserialize the data so it is available in the `$request->getParsedBody()` method call.

Currently this class has support for

- `application/json`
- `application/x-www-form-urlencoded`
- `multipart/form-data`

If no HTTP **content-types** header is provided, this class assumes `application/x-www-form-urlencoded`.

All other **content-types** will be passed through.

This class can be configured to either return the content as an `array` or an `object`. The default is `array`

#### ContentTypeFilter example.

```php
    $request = ServerRequestFactory::fromGlobals(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES,
        new ContentTypeFilter(ContentTypeFilter::ARRAY), // <-- Passing the filter
    );
```

#### About `multipart/form-data`.
The support for `multipart/form-data` is very simple. It does not support file upload. It only reads the `Content-Disposition` header. It ignores `Content-Transfer-Encoding` and `Content-Type` as well as others.


### ServerFilterAggregate
Because the `ServerRequestFactory::fromGlobals` method only accepts one `FilterServerRequestInterface` argument, and because maybe one needs to apply multiple filters, an aggregate utility class might come in handy.

That is what this is. It is a utility class that takes in an array of `FilterServerRequestInterface`s and runs them one after the other before returning the last filtered request.

Under the hood, this is a very simple implementation, it pretty much _reduces_ all the filters down to one
```php
public function __invoke(ServerRequestInterface $request): ServerRequestInterface
{
    return array_reduce($this->filters, fn (
        ServerRequestInterface $request, 
        FilterServerRequestInterface $filter) => $filter($request)
    , $request);
}
```

As you can see from the code snipped. The order of the filters is the order of the array (if the order is important). This is how this would be used
```php
    $request = ServerRequestFactory::fromGlobals(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES,
        new ServerFilterAggregate([ // <-- Passing multiple filters
            FilterUsingXForwardedHeaders::trustReservedSubnets(),
            new ContentTypeFilter(ContentTypeFilter::ARRAY)
        ])
    );
```
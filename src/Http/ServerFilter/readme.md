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

This class can be configued to either return the content as an `array` or an `object`. The default is `array`

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

### About `multipart/form-data`.
The support for `multipart/form-data` is very simple. It does not support file upload. It only reads the `Content-Disposition` header. It ignores `Content-Transfer-Encoding` and `Content-Type` as well as others.

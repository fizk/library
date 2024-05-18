## Response

Different response object that implement [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface).

### ServerErrorResponse
Used to return a HTTP response when an Exception is thrown. It takes as its argument an `Throwable` and converts it into a JSON object to be sent back to the client.

By default it will issue a **500 Internal Server Error** status code, but that can be changed by passing in a different integer code as its second argument.


```php
try {
    // ...
}
catch (Throwable $throwable) {
    $response = new ServerErrorResponse($throwable);
    $emitter->emit($response);
}
```

### ClientErrorResponse
Used to return a HTTP response when a [Form](https://github.com/is-tonlist/it-resource/blob/main/library/Form/Form.php) is invalid. It takes as its argument the `Form` and converts the Form's messages into a JSON object to be sent back to the client.

By default it will issue a **400 Bad Request** status code, but that can be changed by passing in a different integer code as its second argument.


```php

$form = new SomeForm(
    $request->getParsedBody()
);

if ($form->isValid()) {
    //... do something with the form
} else {
    return new ClientErrorResponse($form)
}
```

<?php

use Laminas\Diactoros\ServerRequestFactory;
use Library\Http\ServerFilter\ContentTypeFilter;

require '../vendor/autoload.php';

// [$input] = request_parse_body();
var_dump($_SERVER['CONTENT_TYPE']);
// var_dump($input);

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES,
    new ContentTypeFilter(ContentTypeFilter::ARRAY),
);

// print_r($_SERVER);
print_r($request->getParsedBody());

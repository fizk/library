<?php

use Laminas\Diactoros\ServerRequestFactory;
use Library\Http\ServerFilter\ContentTypeFilter;

require '../vendor/autoload.php';

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

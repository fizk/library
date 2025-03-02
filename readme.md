# Library

www/index.php
```php
<?php
chdir(dirname(__DIR__));
include __DIR__ . '/../vendor/autoload.php';

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Library\Container\Container;
use Library\Http\Response\ServerErrorResponse;
use Library\Http\ServerFilter\ContentTypeFilter;
use Library\Router\Router;

set_error_handler(function ($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$emitter = new SapiEmitter();

try {
    $request = ServerRequestFactory::fromGlobals(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES,
        new ContentTypeFilter(ContentTypeFilter::ARRAY),
    );

    $serviceManager = new Container( require './config/service.php');
    $router = new Router(require './config/router.php');

    $routeMatch = $router->match($request);
    if (!$routeMatch) {
        $emitter->emit(new EmptyResponse(406));
        exit;
    }
    $controllerName = $routeMatch->getValue();
    $attributes = $routeMatch->getAttributes();

    foreach($attributes as $key => $value) {
        $request = $request->withAttribute($key, $value);
    }

    /** @var \Psr\Http\Server\RequestHandlerInterface */
    $controller = $serviceManager->get($controllerName);
    $response = $controller->handle($request);

    $emitter->emit($response);
    exit;
}
catch (Throwable $throwable) {
    $response = new ServerErrorResponse($throwable);
    $emitter->emit($response);
    exit;
}
```

or you can use the `run` function
```php
chdir(dirname(__DIR__));
include __DIR__ . '/../vendor/autoload.php';

use Library\Container\Container;
use Library\Router\Router;
use function Library\run;

set_error_handler(function ($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

$serviceManager = new Container( require './config/service.php');
$router = new Router(require './config/router.php');

run($serviceManager, $router);
```

composer.json
```json
{
    "require": {
        "fizk/library": "0.0.1",
        "laminas/laminas-diactoros": "^3.3",
        "laminas/laminas-httphandlerrunner": "^2.10"
    }
}

```

config/router.php
```php
<?php

return [
    'index' => [
        '/^\/$/' => 'ClassName',
    ],
    'artist' => [],
];
```

config/service.php
```php

<?php

use Psr\Container\ContainerInterface;

return [
    'controllers' => [
        stdClass::class => function (ContainerInterface $container) {
            return new \stdClass;
        },
    ],

    'services' => [],

    'utils' => []
];
```

dockerfile
```dockerfile
FROM php:8.3.1-apache-bookworm

ARG ENV=production
ENV ENV=$ENV

RUN apt-get update; \
     apt-get upgrade; \
     apt-get install git zip -y;

RUN  docker-php-ext-install pdo_mysql

WORKDIR /var/app

RUN echo "<VirtualHost *:80> \n\
    ServerAdmin webmaster@localhost \n\
    DocumentRoot /var/app/www \n\
    ErrorLog \${APACHE_LOG_DIR}/error.log \n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined \n\
    <Directory /var/app/www/> \n\
        Options Indexes FollowSymLinks \n\
        AllowOverride None \n\
        Require all granted \n\n\
        \
        RewriteEngine on \n\
        RewriteCond %{REQUEST_FILENAME} !-d \n\
        RewriteCond %{REQUEST_FILENAME} !-f \n\
        RewriteRule . index.php [L] \n\
    </Directory> \n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf;

RUN a2enmod rewrite;

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=2.6.6

RUN if [ "$ENV" != "production" ] ; then \
    pecl install xdebug-3.3.1; \
    docker-php-ext-enable xdebug; \
    echo "error_reporting = E_ALL\n\
display_startup_errors = On\n\
display_errors = On\n\
xdebug.mode = debug\n\
xdebug.start_with_request=yes\n\
xdebug.client_host=host.docker.internal\n\
xdebug.client_port=9003\n\
xdebug.idekey=myKey\n\
xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;
    fi ;


WORKDIR /var/app

COPY ./composer.json ./composer.json
COPY ./composer.lock ./composer.lock

RUN if [ "$ENV" != "production" ] ; then \
    composer install --no-interaction --no-cache; \
    composer dump-autoload; \
fi ;

RUN if [ "$ENV" = "production" ] ; then \
    composer install --no-interaction --no-dev --no-cache -o; \
    composer dump-autoload -o; \
fi ;


COPY bin bin
COPY www www
COPY src src
COPY library library
COPY config config


CMD ["bash", "-c", "composer db-migrate -- $(pwd)/schema; apache2-foreground"]

```

database migration script
```php
#!/usr/local/bin/php

<?php
chdir(dirname(__DIR__));
include __DIR__ . '/../vendor/autoload.php';
set_error_handler(function ($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

use Library\Container\Container;

$scriptDirectory = $argv[1] ?? './schema';

$serviceManager = new Container(require './config/service.php');

/** @var PDO */
$pdo = $serviceManager->get(PDO::class);


echo "Starting database migration " . PHP_EOL;

$scriptPaths = array_map(
    fn (string $path) => new SplFileInfo(realpath("$scriptDirectory/$path")),
    scandir(realpath($scriptDirectory))
);

foreach ($scriptPaths as $fileInfo) {
    if ($fileInfo->getExtension() !== 'sql') {
        continue;
    }

    try {
        $statusStatement = $pdo->prepare('select * from __history where id = :name');
        $statusStatement->execute(['name' => $fileInfo->getFilename()]);
        $resultCount = $statusStatement->rowCount();

        if ($resultCount !== 0) {
            echo "Skipping " . $fileInfo->getFilename() . PHP_EOL;
            continue;
        }

        echo "Processing " . $fileInfo->getFilename() . PHP_EOL;

        $script = file_get_contents($fileInfo->getPathname());
        $pdo->exec($script);

        $updateStatement = $pdo->prepare('insert into __history (`id`) values (:name)');
        $updateStatement->execute(['name' => $fileInfo->getFilename()]);
    } catch (Throwable $e) {
        echo $e->getMessage() . ' in migration file "' . $fileInfo->getFilename() .'"' . PHP_EOL;
    }
}

echo "Ending database migration " . PHP_EOL;

```
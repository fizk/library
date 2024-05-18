<?php

namespace Library\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * No entry was found in the container.
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}

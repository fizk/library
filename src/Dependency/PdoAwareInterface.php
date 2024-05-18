<?php

namespace Library\Dependency;

use PDO;

interface PdoAwareInterface
{
    public function getPdo(): PDO;

    public function setPdo(PDO $pdo): static;
}

<?php

namespace Library\Dependency;

use PDO;

trait PdoAwareTrait
{
    private PDO $pdo;

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function setPdo(PDO $pdo): static
    {
        $this->pdo = $pdo;
        return $this;
    }
}

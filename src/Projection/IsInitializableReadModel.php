<?php

declare(strict_types=1);

namespace App\Projection;

trait IsInitializableReadModel
{
    public function isInitialized(): bool
    {
        $statement = $this->connection->prepare(
            sprintf("SHOW TABLES LIKE '%s';", self::TABLE)
        );
        $statement->execute();

        $result = $statement->fetch();

        if (false === $result) {
            return false;
        }

        return true;
    }
}

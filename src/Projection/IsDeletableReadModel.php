<?php

declare(strict_types=1);

namespace App\Projection;

trait IsDeletableReadModel
{
    public function delete(): void
    {
        $statement = $this->connection->prepare(
            sprintf('DROP TABLE `%s`;', self::TABLE)
        );
        $statement->execute();
    }
}

<?php

declare(strict_types=1);

namespace App\Projection;

trait IsResetableReadModel
{
    public function reset(): void
    {
        $statement = $this->connection->prepare(
            sprintf('TRUNCATE TABLE `%s`;', self::TABLE)
        );
        $statement->execute();
    }
}

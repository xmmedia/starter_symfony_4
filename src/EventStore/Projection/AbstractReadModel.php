<?php

declare(strict_types=1);

namespace App\EventStore\Projection;

use Doctrine\DBAL\Connection;

abstract class AbstractReadModel extends \Prooph\EventStore\Projection\AbstractReadModel
{
    /** @var string The table for this read model */
    protected const TABLE = null;

    /** @var Connection */
    protected $connection;

    /**
     * The tables that make up the read model.
     * During the initialization check, reset and delete,
     * all the tables in this array are checked, truncated, or deleted.
     * If the array is empty on construct,
     * the TABLE constant will be put in the array.
     *
     * @var string[]|null
     */
    protected $tables;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        if (null === $this->tables) {
            $this->tables = [static::TABLE];
        }
    }

    public function isInitialized(): bool
    {
        foreach ($this->tables as $table) {
            $statement = $this->connection->prepare(
                sprintf("SHOW TABLES LIKE '%s';", $table)
            );
            $statement->execute();

            $result = $statement->fetch();

            if (false === $result) {
                return false;
            }
        }

        return true;
    }

    public function reset(): void
    {
        foreach ($this->tables as $table) {
            $statement = $this->connection->prepare(
                sprintf('TRUNCATE TABLE `%s`;', $table)
            );
            $statement->execute();
        }
    }

    public function delete(): void
    {
        foreach ($this->tables as $table) {
            $statement = $this->connection->prepare(
                sprintf('DROP TABLE `%s`;', $table)
            );
            $statement->execute();
        }
    }
}

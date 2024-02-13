<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Kernel;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

readonly class MigrationFactory implements \Doctrine\Migrations\Version\MigrationFactory
{
    public function __construct(private Connection $connection, private LoggerInterface $logger, private Kernel $kernel)
    {
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $migration = new $migrationClassName(
            $this->connection,
            $this->logger,
        );

        if (method_exists($migration, 'setKernel')) {
            $migration->setKernel($this->kernel);
        }

        return $migration;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Doctrine;

use App\Doctrine\MigrationFactory;
use App\Kernel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;

class MigrationFactoryTest extends BaseTestCase
{
    public function testCreateVersionInstantiatesMigration(): void
    {
        $schemaManager = \Mockery::mock(\Doctrine\DBAL\Schema\AbstractSchemaManager::class);
        $platform = \Mockery::mock(\Doctrine\DBAL\Platforms\AbstractPlatform::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('createSchemaManager')
            ->andReturn($schemaManager);
        $connection->shouldReceive('getDatabasePlatform')
            ->andReturn($platform);

        $logger = \Mockery::mock(LoggerInterface::class);
        $kernel = \Mockery::mock(Kernel::class);

        $factory = new MigrationFactory($connection, $logger, $kernel);

        $migration = $factory->createVersion(TestMigration::class);

        $this->assertInstanceOf(AbstractMigration::class, $migration);
        $this->assertInstanceOf(TestMigration::class, $migration);
    }

    public function testCreateVersionPassesConnectionAndLogger(): void
    {
        $schemaManager = \Mockery::mock(\Doctrine\DBAL\Schema\AbstractSchemaManager::class);
        $platform = \Mockery::mock(\Doctrine\DBAL\Platforms\AbstractPlatform::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('createSchemaManager')
            ->andReturn($schemaManager);
        $connection->shouldReceive('getDatabasePlatform')
            ->andReturn($platform);

        $logger = \Mockery::mock(LoggerInterface::class);
        $kernel = \Mockery::mock(Kernel::class);

        $factory = new MigrationFactory($connection, $logger, $kernel);

        $migration = $factory->createVersion(TestMigration::class);

        // Verify migration was constructed with connection and logger
        $this->assertInstanceOf(TestMigration::class, $migration);

        // Access the connection via reflection to verify it was set
        $reflection = new \ReflectionClass($migration);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);

        $this->assertSame($connection, $connectionProperty->getValue($migration));
    }

    public function testCreateVersionSetsKernelIfMethodExists(): void
    {
        $schemaManager = \Mockery::mock(\Doctrine\DBAL\Schema\AbstractSchemaManager::class);
        $platform = \Mockery::mock(\Doctrine\DBAL\Platforms\AbstractPlatform::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('createSchemaManager')
            ->andReturn($schemaManager);
        $connection->shouldReceive('getDatabasePlatform')
            ->andReturn($platform);

        $logger = \Mockery::mock(LoggerInterface::class);
        $kernel = \Mockery::mock(Kernel::class);

        $factory = new MigrationFactory($connection, $logger, $kernel);

        $migration = $factory->createVersion(TestMigrationWithKernel::class);

        $this->assertInstanceOf(TestMigrationWithKernel::class, $migration);
        $this->assertSame($kernel, $migration->getKernel());
    }

    public function testCreateVersionDoesNotSetKernelIfMethodDoesNotExist(): void
    {
        $schemaManager = \Mockery::mock(\Doctrine\DBAL\Schema\AbstractSchemaManager::class);
        $platform = \Mockery::mock(\Doctrine\DBAL\Platforms\AbstractPlatform::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('createSchemaManager')
            ->andReturn($schemaManager);
        $connection->shouldReceive('getDatabasePlatform')
            ->andReturn($platform);

        $logger = \Mockery::mock(LoggerInterface::class);
        $kernel = \Mockery::mock(Kernel::class);

        $factory = new MigrationFactory($connection, $logger, $kernel);

        // Should not throw an error even though setKernel doesn't exist
        $migration = $factory->createVersion(TestMigration::class);

        $this->assertInstanceOf(TestMigration::class, $migration);
    }

    public function testCreateVersionWithDifferentMigrationClass(): void
    {
        $schemaManager = \Mockery::mock(\Doctrine\DBAL\Schema\AbstractSchemaManager::class);
        $platform = \Mockery::mock(\Doctrine\DBAL\Platforms\AbstractPlatform::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('createSchemaManager')
            ->twice()
            ->andReturn($schemaManager);
        $connection->shouldReceive('getDatabasePlatform')
            ->twice()
            ->andReturn($platform);

        $logger = \Mockery::mock(LoggerInterface::class);
        $kernel = \Mockery::mock(Kernel::class);

        $factory = new MigrationFactory($connection, $logger, $kernel);

        $migration1 = $factory->createVersion(TestMigration::class);
        $migration2 = $factory->createVersion(TestMigrationWithKernel::class);

        $this->assertInstanceOf(TestMigration::class, $migration1);
        $this->assertInstanceOf(TestMigrationWithKernel::class, $migration2);
        $this->assertNotSame($migration1, $migration2);
    }
}

// Test migration classes
class TestMigration extends AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema): void
    {
        // Test migration - no implementation needed
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema): void
    {
        // Test migration - no implementation needed
    }
}

class TestMigrationWithKernel extends AbstractMigration
{
    private ?Kernel $kernel = null;

    public function setKernel(Kernel $kernel): void
    {
        $this->kernel = $kernel;
    }

    public function getKernel(): ?Kernel
    {
        return $this->kernel;
    }

    public function up(\Doctrine\DBAL\Schema\Schema $schema): void
    {
        // Test migration - no implementation needed
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema): void
    {
        // Test migration - no implementation needed
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Projection\AuthLog;

use App\Projection\AuthLog\AuthLogReadModel;
use App\Projection\Table;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;

class AuthLogReadModelTest extends BaseTestCase
{
    public function testInit(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->twice()
            ->andReturn(\Mockery::mock(Result::class));

        new AuthLogReadModel($connection)->init();
    }

    public function testInitCreatesTableSql(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->with(\Mockery::on(static fn ($sql): bool => str_contains($sql, 'CREATE TABLE')
                && str_contains($sql, Table::AUTH_LOG)
                && str_contains($sql, 'auth_log_id')
                && str_contains($sql, 'event_type')
                && str_contains($sql, 'occurred_at')))
            ->andReturn(\Mockery::mock(Result::class));

        $connection->shouldReceive('executeQuery')
            ->once()
            ->with(\Mockery::on(static fn ($sql): bool => str_contains($sql, 'ALTER TABLE')
                && str_contains($sql, 'ADD PRIMARY KEY')))
            ->andReturn(\Mockery::mock(Result::class));

        new AuthLogReadModel($connection)->init();
    }

    public function testInsert(): void
    {
        $faker = $this->faker();

        $data = [
            'auth_log_id' => $faker->uuid(),
            'event_type'  => 'login',
            'user_id'     => $faker->uuid(),
            'ip_address'  => $faker->ipv4(),
            'occurred_at' => new \DateTimeImmutable(),
        ];
        $types = ['occurred_at' => 'datetime_immutable'];

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')
            ->once()
            ->with(Table::AUTH_LOG, $data, $types);

        $readModel = new AuthLogReadModel($connection);

        $method = new \ReflectionClass($readModel)->getMethod('insert');
        $method->invoke($readModel, $data, $types);
    }
}

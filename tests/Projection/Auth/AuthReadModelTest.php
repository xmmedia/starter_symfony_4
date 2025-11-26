<?php

declare(strict_types=1);

namespace App\Tests\Projection\Auth;

use App\Projection\Auth\AuthReadModel;
use App\Projection\Table;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;

class AuthReadModelTest extends BaseTestCase
{
    public function testInitDoesNotCreateTable(): void
    {
        $connection = \Mockery::mock(Connection::class);
        // Should not receive any calls for table creation
        $connection->shouldNotReceive('executeQuery');

        $readModel = new AuthReadModel($connection);
        $readModel->init();

        // If we get here without exception, init() did nothing as expected
        $this->assertTrue(true);
    }

    public function testLoggedInUpdatesUserLoginCount(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid();
        $lastLogin = new \DateTimeImmutable('2024-01-15 10:30:00');

        $statement = \Mockery::mock(Statement::class);
        $statement->shouldReceive('bindValue')
            ->once()
            ->with('last_login', $lastLogin, 'datetime_immutable');
        $statement->shouldReceive('bindValue')
            ->once()
            ->with(':user_id', $userId);
        $statement->shouldReceive('executeQuery')
            ->once()
            ->andReturn(\Mockery::mock(Result::class));

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('prepare')
            ->once()
            ->with(\Mockery::on(function ($sql) {
                return str_contains($sql, 'UPDATE `user`')
                    && str_contains($sql, 'login_count = login_count + 1')
                    && str_contains($sql, 'last_login = :last_login')
                    && str_contains($sql, 'WHERE user_id = :user_id');
            }))
            ->andReturn($statement);

        $readModel = new AuthReadModel($connection);

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($readModel);
        $method = $reflection->getMethod('loggedIn');
        $method->setAccessible(true);
        $method->invoke($readModel, $userId, $lastLogin);
    }

    public function testResetCallsDelete(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->with(\Mockery::on(function ($sql) {
                return str_contains($sql, 'UPDATE `user`')
                    && str_contains($sql, 'login_count = 0')
                    && str_contains($sql, 'last_login = null');
            }))
            ->andReturn(\Mockery::mock(Result::class));

        $readModel = new AuthReadModel($connection);
        $readModel->reset();
    }

    public function testDeleteResetsLoginCountAndLastLogin(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->once()
            ->with(\Mockery::on(function ($sql) {
                return str_contains($sql, 'UPDATE `user`')
                    && str_contains($sql, 'SET login_count = 0, last_login = null');
            }))
            ->andReturn(\Mockery::mock(Result::class));

        $readModel = new AuthReadModel($connection);
        $readModel->delete();
    }

    public function testStackMethodExists(): void
    {
        $this->assertTrue(method_exists(AuthReadModel::class, 'stack'));
    }
}

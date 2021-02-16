<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Projection\User\UserReadModel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Mockery;

class UserReadModelTest extends BaseTestCase
{
    public function testInit(): void
    {
        $statement = Mockery::mock(Statement::class);
        $statement->shouldReceive('execute')
            ->twice();

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('prepare')
            ->twice()
            ->withArgs(function (string $sql) {
                return (bool) strpos($sql, '`user`');
            })
            ->andReturn($statement);

        (new UserReadModel($connection))->init();
    }

    public function testInsert(): void
    {
        $faker = $this->faker();
        $data = $types = ['key' => $faker->string(5)];

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedData,
                    array $passedTypes
                ) use ($data, $types): bool {
                    $this->assertEquals('user', $table);
                    $this->assertEquals($data, $passedData);
                    $this->assertEquals($types, $passedTypes);

                    return true;
                }
            );

        $reflection = new \ReflectionClass(UserReadModel::class);
        $method = $reflection->getMethod('insert');
        $method->setAccessible(true);

        $method->invokeArgs(new UserReadModel($connection), [$data, $types]);
    }

    public function testUpdate(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid;
        $data = $types = ['key' => $faker->string(5)];

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('update')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedData,
                    array $passedCriteria,
                    array $passedTypes
                ) use ($userId, $data, $types): bool {
                    $this->assertEquals('user', $table);
                    $this->assertEquals($data, $passedData);
                    $this->assertEquals(['user_id' => $userId], $passedCriteria);
                    $this->assertEquals($types, $passedTypes);

                    return true;
                }
            );

        $reflection = new \ReflectionClass(UserReadModel::class);
        $method = $reflection->getMethod('update');
        $method->setAccessible(true);

        $method->invokeArgs(new UserReadModel($connection), [$userId, $data, $types]);
    }

    public function testLoggedIn(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid;
        $dateTime = \DateTimeImmutable::createFromMutable($faker->dateTime);

        $statement = Mockery::mock(Statement::class);
        $statement->shouldReceive('bindValue')
            ->once()
            ->with('last_login', $dateTime, 'datetime');
        $statement->shouldReceive('bindValue')
            ->once()
            ->with('user_id', $userId);
        $statement->shouldReceive('execute')
            ->once();

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('prepare')
            ->once()
            ->withArgs(function (string $sql) {
                return (bool) strpos($sql, '`user`');
            })
            ->andReturn($statement);

        $reflection = new \ReflectionClass(UserReadModel::class);
        $method = $reflection->getMethod('loggedIn');
        $method->setAccessible(true);

        $method->invokeArgs(new UserReadModel($connection), [$userId, $dateTime]);
    }
}

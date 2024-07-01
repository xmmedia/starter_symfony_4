<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Projection\User\UserReadModel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;

class UserReadModelTest extends BaseTestCase
{
    public function testInit(): void
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->twice()
            ->withArgs(static fn (string $sql): bool => (bool) strpos($sql, '`user`'));

        (new UserReadModel($connection))->init();
    }

    public function testInsert(): void
    {
        $faker = $this->faker();
        $data = $types = ['key' => $faker->string(5)];

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedData,
                    array $passedTypes,
                ) use ($data, $types): bool {
                    $this->assertEquals('user', $table);
                    $this->assertEquals($data, $passedData);
                    $this->assertEquals($types, $passedTypes);

                    return true;
                },
            );

        $reflection = new \ReflectionClass(UserReadModel::class);
        $method = $reflection->getMethod('insert');

        $method->invokeArgs(new UserReadModel($connection), [$data, $types]);
    }

    public function testUpdate(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid();
        $data = $types = ['key' => $faker->string(5)];

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('update')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedData,
                    array $passedCriteria,
                    array $passedTypes,
                ) use ($userId, $data, $types): bool {
                    $this->assertEquals('user', $table);
                    $this->assertEquals($data, $passedData);
                    $this->assertEquals(['user_id' => $userId], $passedCriteria);
                    $this->assertEquals($types, $passedTypes);

                    return true;
                },
            );

        $reflection = new \ReflectionClass(UserReadModel::class);
        $method = $reflection->getMethod('update');

        $method->invokeArgs(new UserReadModel($connection), [$userId, $data, $types]);
    }

    public function testRemove(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid();

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('delete')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedCriteria,
                ) use ($userId): bool {
                    $this->assertEquals('user', $table);
                    $this->assertEquals(['user_id' => $userId], $passedCriteria);

                    return true;
                },
            );

        $reflection = new \ReflectionClass(UserReadModel::class);
        $method = $reflection->getMethod('remove');

        $method->invokeArgs(new UserReadModel($connection), [$userId]);
    }
}

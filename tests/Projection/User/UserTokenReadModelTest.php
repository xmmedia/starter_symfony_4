<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Projection\User\UserTokenReadModel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Mockery;

class UserTokenReadModelTest extends BaseTestCase
{
    public function testInit(): void
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('executeQuery')
            ->twice()
            ->withArgs(function (string $sql) {
                return (bool) strpos($sql, '`user_token`');
            });

        (new UserTokenReadModel($connection))->init();
    }

    public function testAdd(): void
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
                    $this->assertEquals('user_token', $table);
                    $this->assertEquals($data, $passedData);
                    $this->assertEquals($types, $passedTypes);

                    return true;
                }
            );

        $reflection = new \ReflectionClass(UserTokenReadModel::class);
        $method = $reflection->getMethod('add');
        $method->setAccessible(true);

        $method->invokeArgs(new UserTokenReadModel($connection), [$data, $types]);
    }

    public function testRemoveAllForUser(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid();

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('delete')
            ->once()
            ->withArgs(
                function (
                    string $table,
                    array $passedCriteria
                ) use ($userId): bool {
                    $this->assertEquals('user_token', $table);
                    $this->assertEquals(['user_id' => $userId], $passedCriteria);

                    return true;
                }
            );

        $reflection = new \ReflectionClass(UserTokenReadModel::class);
        $method = $reflection->getMethod('removeAllForUser');
        $method->setAccessible(true);

        $method->invokeArgs(new UserTokenReadModel($connection), [$userId]);
    }
}

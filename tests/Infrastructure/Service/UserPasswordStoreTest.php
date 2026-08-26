<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\Service\UserPasswordStore;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;

class UserPasswordStoreTest extends BaseTestCase
{
    public function testStore(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $hashedPassword = $faker->password();

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('executeStatement')
            ->once()
            ->with(
                \Mockery::on(
                    static fn (string $sql): bool => str_contains($sql, 'INSERT INTO `user_credential`')
                        && str_contains($sql, 'ON DUPLICATE KEY UPDATE'),
                ),
                \Mockery::on(
                    static fn (array $params): bool => $params[0] === $userId->toString()
                        && $params[1] === $hashedPassword,
                ),
            );

        new UserPasswordStore($connection)->store($userId, $hashedPassword);
    }

    public function testRemove(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('delete')
            ->once()
            ->with('user_credential', ['user_id' => $userId->toString()]);

        new UserPasswordStore($connection)->remove($userId);
    }
}

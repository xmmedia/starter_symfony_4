<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Infrastructure\Service\UserPasswordStore;
use App\Model\User\Event\UserWasDeletedByAdmin;
use App\ProcessManager\UserDeletedProcessManager;
use App\Tests\BaseTestCase;

class UserDeletedProcessManagerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $passwordStore = \Mockery::mock(UserPasswordStore::class);
        $passwordStore->shouldReceive('remove')
            ->once()
            ->with(\Mockery::on(static fn ($id): bool => $id->sameValueAs($userId)));

        (new UserDeletedProcessManager($passwordStore))(UserWasDeletedByAdmin::now($userId));
    }
}

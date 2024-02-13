<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\AdminDeleteUser;
use App\Tests\BaseTestCase;

class AdminDeleteUserTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = AdminDeleteUser::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}

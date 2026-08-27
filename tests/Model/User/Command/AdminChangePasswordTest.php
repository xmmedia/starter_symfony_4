<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\AdminChangePassword;
use App\Tests\BaseTestCase;

class AdminChangePasswordTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = AdminChangePassword::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}

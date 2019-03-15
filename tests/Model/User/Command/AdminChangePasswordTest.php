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

        $userId = $faker->userId;
        $password = $faker->password(12, 250);

        $command = AdminChangePassword::with($userId, $password);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertEquals($password, $command->encodedPassword());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\ChangePassword;
use App\Tests\BaseTestCase;

class ChangePasswordTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password(12, 250);

        $command = ChangePassword::forUser($userId, $password);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertEquals($password, $command->encodedPassword());
    }
}

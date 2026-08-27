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

        $userId = $faker->userId();

        $command = ChangePassword::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}

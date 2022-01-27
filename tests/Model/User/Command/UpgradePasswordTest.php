<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\UpgradePassword;
use App\Tests\BaseTestCase;

class UpgradePasswordTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $password = $faker->password();

        $command = UpgradePassword::forUser($userId, $password);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertEquals($password, $command->hashedPassword());
    }
}

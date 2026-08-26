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

        $command = UpgradePassword::forUser($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\UserLoggedIn;
use App\Tests\BaseTestCase;

class UserLoggedInTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = UserLoggedIn::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}

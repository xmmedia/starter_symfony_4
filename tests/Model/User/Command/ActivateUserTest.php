<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\ActivateUser;
use App\Tests\BaseTestCase;

class ActivateUserTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = ActivateUser::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}

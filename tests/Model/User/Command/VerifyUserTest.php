<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\VerifyUser;
use App\Tests\BaseTestCase;

class VerifyUserTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = VerifyUser::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Tests\BaseTestCase;

class InitiatePasswordRecoveryTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();

        $command = InitiatePasswordRecovery::now($userId, $email);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\Email;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\UserId;
use Faker;
use PHPUnit\Framework\TestCase;

class InitiatePasswordRecoveryTest extends TestCase
{
    public function test(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);

        $command = InitiatePasswordRecovery::now($userId, $email);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
    }
}

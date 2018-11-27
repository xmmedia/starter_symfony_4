<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\Email;
use App\Model\User\Command\SendActivation;
use App\Model\User\Name;
use App\Model\User\UserId;
use Faker;
use PHPUnit\Framework\TestCase;

class SendActivationTest extends TestCase
{
    public function test(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = SendActivation::now($userId, $email, $firstName, $lastName);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertTrue($firstName->sameValueAs($command->firstName()));
        $this->assertTrue($lastName->sameValueAs($command->lastName()));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Command;

use App\Model\Auth\AuthId;
use App\Model\Auth\Command\UserLoggedInSuccessfully;
use App\Model\Email;
use App\Model\User\UserId;
use Faker;
use PHPUnit\Framework\TestCase;

class UserLoggedInSuccessfullyTest extends TestCase
{
    public function test(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;

        $command = UserLoggedInSuccessfully::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress
        );

        $this->assertTrue($authId->sameValueAs($command->authId()));
        $this->assertEquals($userId, $command->userId());
        $this->assertEquals($email, $command->email());
        $this->assertEquals($userAgent, $command->userAgent());
        $this->assertEquals($ipAddress, $command->ipAddress());
    }
}

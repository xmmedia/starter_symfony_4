<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\ChangePassword;
use App\Model\User\UserId;
use Faker;
use PHPUnit\Framework\TestCase;

class ChangePasswordTest extends TestCase
{
    public function test(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $password = $faker->password;

        $command = ChangePassword::forUser($userId, $password);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertEquals($password, $command->encodedPassword());
    }
}

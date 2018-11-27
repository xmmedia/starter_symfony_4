<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\Email;
use App\Model\User\Command\AdminCreateUserMinimum;
use App\Model\User\UserId;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

class AdminCreateUserMinimumTest extends TestCase
{
    public function test(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');

        $command = AdminCreateUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertEquals($password, $command->encodedPassword());
        $this->assertEquals($role, $command->role());
    }
}

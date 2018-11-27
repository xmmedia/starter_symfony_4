<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\Email;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Name;
use App\Model\User\UserId;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

class AdminUpdateUserTest extends TestCase
{
    public function test(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminUpdateUser::with(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName
        );

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertEquals($role, $command->role());
        $this->assertTrue($firstName->sameValueAs($command->firstName()));
        $this->assertTrue($lastName->sameValueAs($command->lastName()));
    }
}

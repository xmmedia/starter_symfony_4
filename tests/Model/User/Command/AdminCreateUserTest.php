<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\Email;
use App\Model\User\Command\AdminCreateUser;
use App\Model\User\Name;
use App\Tests\BaseTestCase;
use Symfony\Component\Security\Core\Role\Role;

class AdminCreateUserTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminCreateUser::with(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false
        );

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertEquals($password, $command->encodedPassword());
        $this->assertEquals($role, $command->role());
        $this->assertTrue($command->active());
        $this->assertTrue($firstName->sameValueAs($command->firstName()));
        $this->assertTrue($lastName->sameValueAs($command->lastName()));
        $this->assertFalse($command->sendInvite());
    }
}

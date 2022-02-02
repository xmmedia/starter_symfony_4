<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Tests\BaseTestCase;

class AdminAddUserMinimumTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();

        $command = AdminAddUserMinimum::with(
            $userId,
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
            $sendInvite,
        );

        $this->assertSameValueAs($userId, $command->userId());
        $this->assertSameValueAs($email, $command->email());
        $this->assertEquals($password, $command->hashedPassword());
        $this->assertEquals($role, $command->role());
        $this->assertSameValueAs($firstName, $command->firstName());
        $this->assertSameValueAs($lastName, $command->lastName());
        $this->assertSame($sendInvite, $command->sendInvite());
    }
}

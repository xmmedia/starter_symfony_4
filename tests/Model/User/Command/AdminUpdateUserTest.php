<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Tests\BaseTestCase;

class AdminUpdateUserTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $command = AdminUpdateUser::with(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName,
            $userData,
        );

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertEquals($role, $command->role());
        $this->assertTrue($firstName->sameValueAs($command->firstName()));
        $this->assertTrue($lastName->sameValueAs($command->lastName()));
        $this->assertSameValueAs($userData, $command->userData());
    }
}

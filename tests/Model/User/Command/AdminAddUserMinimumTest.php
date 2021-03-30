<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\AdminAddUserMinimum;
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

        $command = AdminAddUserMinimum::with(
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

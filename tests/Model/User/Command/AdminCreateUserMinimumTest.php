<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\AdminCreateUserMinimum;
use App\Tests\BaseTestCase;
use Symfony\Component\Security\Core\Role\Role;

class AdminCreateUserMinimumTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password(12, 250);
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

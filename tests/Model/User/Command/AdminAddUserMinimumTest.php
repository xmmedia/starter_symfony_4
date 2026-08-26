<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Name;
use App\Tests\BaseTestCase;

class AdminAddUserMinimumTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $role = $faker->userRole();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();

        $command = AdminAddUserMinimum::with(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName,
            $sendInvite,
        );

        $this->assertSameValueAs($userId, $command->userId());
        $this->assertSameValueAs($email, $command->email());
        $this->assertEquals($role, $command->role());
        $this->assertSameValueAs($firstName, $command->firstName());
        $this->assertSameValueAs($lastName, $command->lastName());
        $this->assertSame($sendInvite, $command->sendInvite());
    }
}

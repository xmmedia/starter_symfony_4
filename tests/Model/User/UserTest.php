<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Role;
use App\Model\User\User;
use App\Tests\BaseTestCase;
use App\Tests\FakeAr;

class UserTest extends BaseTestCase
{
    use UserTestTrait;

    public function testSameIdentityAs(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user1 = User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );
        $user2 = User::createByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertTrue($user1->sameIdentityAs($user2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user1 = User::createByAdminMinimum(
            $faker->userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );
        $user2 = User::createByAdminMinimum(
            $faker->userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertFalse($user1->sameIdentityAs($user2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $user = User::createByAdminMinimum(
            $faker->userId,
            $faker->emailVo,
            $faker->password,
            Role::ROLE_USER(),
            $this->userUniquenessCheckerNone
        );

        $this->assertFalse($user->sameIdentityAs(FakeAr::create()));
    }
}

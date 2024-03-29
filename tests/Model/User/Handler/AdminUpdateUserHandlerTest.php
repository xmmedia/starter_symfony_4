<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\AdminUpdateUserHandler;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;

class AdminUpdateUserHandlerTest extends BaseTestCase
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

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('updateByAdmin')
            ->once();

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(User::class));

        $checksUniqueUsersEmail = \Mockery::mock(ChecksUniqueUsersEmail::class);
        $checksUniqueUsersEmail->shouldReceive('__invoke')
            ->andReturnNull();

        (new AdminUpdateUserHandler($repo, $checksUniqueUsersEmail))($command);
    }

    public function testNotFound(): void
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

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        $checksUniqueUsersEmail = \Mockery::mock(ChecksUniqueUsersEmail::class);
        $checksUniqueUsersEmail->shouldReceive('__invoke')
            ->andReturnNull();

        (new AdminUpdateUserHandler($repo, $checksUniqueUsersEmail))($command);
    }
}

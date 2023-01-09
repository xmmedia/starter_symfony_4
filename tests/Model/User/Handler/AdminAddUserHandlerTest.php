<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\AdminAddUser;
use App\Model\User\Handler\AdminAddUserHandler;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;

class AdminAddUserHandlerTest extends BaseTestCase
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

        $command = AdminAddUser::with(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false,
        );

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(User::class));

        $checksUniqueUsersEmail = \Mockery::mock(ChecksUniqueUsersEmail::class);
        $checksUniqueUsersEmail->shouldReceive('__invoke')
            ->andReturnNull();

        (new AdminAddUserHandler($repo, $checksUniqueUsersEmail))($command);
    }
}

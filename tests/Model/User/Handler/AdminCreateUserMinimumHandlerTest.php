<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\Email;
use App\Model\User\Command\AdminCreateUserMinimum;
use App\Model\User\Exception\DuplicateEmail;
use App\Model\User\Handler\AdminCreateUserMinimumHandler;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;
use Ramsey\Uuid\Uuid;

class AdminCreateUserMinimumHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $command = AdminCreateUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new AdminCreateUserMinimumHandler(
            $repo,
            new AdminCreateUserMinimumHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }

    public function testNonUnique(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $command = AdminCreateUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $repo = Mockery::mock(UserList::class);

        $this->expectException(DuplicateEmail::class);

        (new AdminCreateUserMinimumHandler(
            $repo,
            new AdminCreateUserMinimumHandlerUniquenessCheckerDuplicate()
        ))(
            $command
        );
    }
}

class AdminCreateUserMinimumHandlerUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return null;
    }
}

class AdminCreateUserMinimumHandlerUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return UserId::fromUuid(Uuid::uuid4());
    }
}

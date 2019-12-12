<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Exception\DuplicateEmail;
use App\Model\User\Handler\AdminAddUserMinimumHandler;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserIdInterface;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Model\Email;

class AdminAddUserMinimumHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $command = AdminAddUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new AdminAddUserMinimumHandler(
            $repo,
            new AdminAddUserMinimumHandlerUniquenessCheckerNone()
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

        $command = AdminAddUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $repo = Mockery::mock(UserList::class);

        $this->expectException(DuplicateEmail::class);

        (new AdminAddUserMinimumHandler(
            $repo,
            new AdminAddUserMinimumHandlerUniquenessCheckerDuplicate()
        ))(
            $command
        );
    }
}

class AdminAddUserMinimumHandlerUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface
    {
        return null;
    }
}

class AdminAddUserMinimumHandlerUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface
    {
        return UserId::fromUuid(Uuid::uuid4());
    }
}

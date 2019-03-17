<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\Email;
use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\UpdateUserProfileHandler;
use App\Model\User\Name;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Tests\BaseTestCase;
use Mockery;

class UpdateUserProfileHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = UpdateUserProfile::with(
            $userId,
            $email,
            $firstName,
            $lastName
        );

        $user = Mockery::mock(User::class);
        $user->shouldReceive('update')
            ->once();

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new UpdateUserProfileHandler(
            $repo,
            new UpdateUserProfileHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = UpdateUserProfile::with(
            $userId,
            $email,
            $firstName,
            $lastName
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new UpdateUserProfileHandler(
            $repo,
            new UpdateUserProfileHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }
}

class UpdateUserProfileHandlerUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return null;
    }
}

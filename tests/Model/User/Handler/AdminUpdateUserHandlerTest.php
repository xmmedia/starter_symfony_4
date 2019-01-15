<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\Email;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Exception\DuplicateEmailAddress;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\AdminUpdateUserHandler;
use App\Model\User\Name;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

class AdminUpdateUserHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminUpdateUser::with(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName
        );

        $user = Mockery::mock(User::class);
        $user->shouldReceive('updateByAdmin')
            ->once();

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new AdminUpdateUserHandler(
            $repo,
            new AdminUpdateUserHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }

    public function testNotFound(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminUpdateUser::with(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new AdminUpdateUserHandler(
            $repo,
            new AdminUpdateUserHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }
}

class AdminUpdateUserHandlerUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return null;
    }
}

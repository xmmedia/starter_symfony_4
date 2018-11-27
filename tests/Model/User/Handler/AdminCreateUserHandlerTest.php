<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\Email;
use App\Model\User\Command\AdminCreateUser;
use App\Model\User\Exception\DuplicateEmailAddress;
use App\Model\User\Handler\AdminCreateUserHandler;
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

class AdminCreateUserHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminCreateUser::with(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new AdminCreateUserHandler(
            $repo,
            new AdminCreateUserHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }

    public function testNonUnique(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminCreateUser::with(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false
        );

        $repo = Mockery::mock(UserList::class);

        $this->expectException(DuplicateEmailAddress::class);

        (new AdminCreateUserHandler(
            $repo,
            new AdminCreateUserHandlerUniquenessCheckerDuplicate()
        ))(
            $command
        );
    }
}

class AdminCreateUserHandlerUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return null;
    }
}

class AdminCreateUserHandlerUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return UserId::generate();
    }
}

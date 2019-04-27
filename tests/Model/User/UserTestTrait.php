<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Infrastructure\Service\ChecksUniqueUsersEmailFromReadModel;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Mockery;
use Ramsey\Uuid\Uuid;

trait UserTestTrait
{
    /** @var ChecksUniqueUsersEmailFromReadModel|\Mockery\MockInterface */
    private $userUniquenessCheckerNone;

    /** @var ChecksUniqueUsersEmailFromReadModel|\Mockery\MockInterface */
    private $userUniquenessCheckerDuplicate;

    protected function setUp(): void
    {
        $this->userUniquenessCheckerNone = Mockery::spy(
            new ChecksUniqueUsersEmailFromReadModel(
                Mockery::mock(UserFinder::class)
            )
        );
        $this->userUniquenessCheckerNone->shouldReceive('__invoke')
            ->andReturnNull()
            ->byDefault();

        $this->userUniquenessCheckerDuplicate = Mockery::spy(
            new ChecksUniqueUsersEmailFromReadModel(
                Mockery::mock(UserFinder::class)
            )
        );
        $this->userUniquenessCheckerDuplicate->shouldReceive('__invoke')
            ->andReturn(UserId::fromUuid(Uuid::uuid4()))
            ->byDefault();
    }

    private function getUserActive(): User
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false,
            $this->userUniquenessCheckerNone
        );
        $this->popRecordedEvent($user);

        return $user;
    }

    private function getUserInactive(): User
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            false,
            $firstName,
            $lastName,
            false,
            $this->userUniquenessCheckerNone
        );
        $this->popRecordedEvent($user);

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Infrastructure\Service\ChecksUniqueUsersEmailFromReadModel;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Ramsey\Uuid\Uuid;

trait UserTestTrait
{
    private ChecksUniqueUsersEmailFromReadModel|\Mockery\MockInterface $userUniquenessCheckerNone;
    private ChecksUniqueUsersEmailFromReadModel|\Mockery\MockInterface $userUniquenessCheckerDuplicate;

    protected function setUp(): void
    {
        $this->userUniquenessCheckerNone = \Mockery::mock(ChecksUniqueUsersEmail::class);
        $this->userUniquenessCheckerNone->shouldReceive('__invoke')
            ->andReturnNull()
            ->byDefault();

        $this->userUniquenessCheckerDuplicate = \Mockery::spy(ChecksUniqueUsersEmail::class);
        $this->userUniquenessCheckerDuplicate->shouldReceive('__invoke')
            ->andReturn(UserId::fromUuid(Uuid::uuid4()))
            ->byDefault();
    }

    private function getUserActive(bool $sendInvite = false): User
    {
        return $this->getUser(true, $sendInvite);
    }

    private function getUserInactive(bool $sendInvite = false): User
    {
        return $this->getUser(false, $sendInvite);
    }

    private function getUser(bool $active, bool $sendInvite): User
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            $active,
            $firstName,
            $lastName,
            $sendInvite,
            $userData,
            $this->userUniquenessCheckerNone,
        );
        $this->popRecordedEvent($user);

        return $user;
    }
}

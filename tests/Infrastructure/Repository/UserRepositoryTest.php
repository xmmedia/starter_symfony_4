<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\UserRepository;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Util\Tests\AggregateRepositoryFactory;

class UserRepositoryTest extends BaseTestCase
{
    use AggregateRepositoryFactory;

    public function testSaveGet(): void
    {
        $faker = $this->faker();

        $checksUniqueUsersEmail = \Mockery::mock(ChecksUniqueUsersEmail::class);
        $checksUniqueUsersEmail->shouldReceive('__invoke')
            ->andReturnNull();

        $user = User::addByAdminMinimum(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->boolean(),
            $checksUniqueUsersEmail,
        );

        /** @var UserRepository $repository */
        $repository = $this->getRepository(UserRepository::class, User::class);

        $repository->save($user);

        $fetchedUser = $repository->get($user->userId());

        $this->assertInstanceOf(User::class, $fetchedUser);
        $this->assertNotSame($user, $fetchedUser);
        $this->assertSameValueAs($user->userId(), $fetchedUser->userId());
    }

    public function testGetDoesntExist(): void
    {
        $faker = $this->faker();

        /** @var UserRepository $repository */
        $repository = $this->getRepository(UserRepository::class, User::class);
        $fetchedUser = $repository->get($faker->userId());

        $this->assertNull($fetchedUser);
    }
}

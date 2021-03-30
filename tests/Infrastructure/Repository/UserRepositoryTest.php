<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\UserRepository;
use App\Model\User\Role;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\User;
use App\Tests\BaseTestCase;
use Mockery;
use Xm\SymfonyBundle\Util\Tests\AggregateRepositoryFactory;

class UserRepositoryTest extends BaseTestCase
{
    use AggregateRepositoryFactory;

    public function testSaveGet(): void
    {
        $faker = $this->faker();

        $checksUniqueUsersEmail = Mockery::mock(ChecksUniqueUsersEmail::class);
        $checksUniqueUsersEmail->shouldReceive('__invoke')
            ->andReturnNull();

        $user = User::addByAdminMinimum(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            $checksUniqueUsersEmail,
        );

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

        $fetchedUser = $this->getRepository(UserRepository::class, User::class)
            ->get($faker->userId());

        $this->assertNull($fetchedUser);
    }
}

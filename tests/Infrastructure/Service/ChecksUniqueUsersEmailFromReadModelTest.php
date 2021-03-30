<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Entity\User;
use App\Infrastructure\Service\ChecksUniqueUsersEmailFromReadModel;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Mockery;

class ChecksUniqueUsersEmailFromReadModelTest extends BaseTestCase
{
    public function testDoesntExist(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with($email)
            ->andReturnNull();

        $result = (new ChecksUniqueUsersEmailFromReadModel($userFinder))($email);

        $this->assertNull($result);
    }

    public function testExists(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();
        $userId = $faker->userId();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($userId);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        $result = (new ChecksUniqueUsersEmailFromReadModel($userFinder))($email);

        $this->assertEquals($userId, $result);
    }
}

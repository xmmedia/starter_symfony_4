<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Entity\User;
use App\Infrastructure\Service\ChecksUniqueUsersEmailFromReadModel;
use App\Model\Email;
use App\Repository\UserRepository;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class ChecksUniqueUsersEmailFromReadModelTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function testTrue(): void
    {
        $faker = $this->faker();

        $email = Email::fromString($faker->email);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('findOneByEmail')
            ->with($email)
            ->andReturn($user);

        (new ChecksUniqueUsersEmailFromReadModel($userRepo))($email);
    }

    public function testFalse(): void
    {
        $faker = $this->faker();

        $email = $faker->emailVo;

        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('findOneByEmail')
            ->with($email)
            ->andReturnNull();

        (new ChecksUniqueUsersEmailFromReadModel($userRepo))($email);
    }
}

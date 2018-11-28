<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Entity\User;
use App\Infrastructure\Service\ChecksUniqueUsersEmailFromReadModel;
use App\Model\Email;
use App\Model\User\UserId;
use App\Repository\UserRepository;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ChecksUniqueUsersEmailFromReadModelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testTrue(): void
    {
        $faker = Faker\Factory::create();

        $email = Email::fromString($faker->email);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('id')
            ->andReturn(UserId::generate());

        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('findOneByEmail')
            ->with($email)
            ->andReturn($user);

        (new ChecksUniqueUsersEmailFromReadModel($userRepo))($email);
    }

    public function testFalse(): void
    {
        $faker = Faker\Factory::create();

        $email = Email::fromString($faker->email);

        $userRepo = Mockery::mock(UserRepository::class);
        $userRepo->shouldReceive('findOneByEmail')
            ->with($email)
            ->andReturnNull();

        (new ChecksUniqueUsersEmailFromReadModel($userRepo))($email);
    }
}

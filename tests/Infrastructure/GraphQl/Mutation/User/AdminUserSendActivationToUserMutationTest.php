<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Mutation\User\AdminUserSendActivationToUserMutation;
use App\Model\User\Command\SendActivation;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

class AdminUserSendActivationToUserMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendActivation::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($faker->email()));
        $user->shouldReceive('firstName')
            ->once()
            ->andReturn(Name::fromString($faker->firstName()));
        $user->shouldReceive('lastName')
            ->once()
            ->andReturn(Name::fromString($faker->lastName()));

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $result = (new AdminUserSendActivationToUserMutation(
            $commandBus,
            $userFinder,
        ))($userId);

        $this->assertEquals(['userId' => $userId], $result);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->uuid();

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserError::class);

        (new AdminUserSendActivationToUserMutation(
            $commandBus,
            $userFinder,
        ))($userId);
    }
}

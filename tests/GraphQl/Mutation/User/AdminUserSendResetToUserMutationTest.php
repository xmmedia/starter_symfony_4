<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\GraphQl\Mutation\User\AdminUserSendResetToUserMutation;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use Xm\SymfonyBundle\Model\Email;

class AdminUserSendResetToUserMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(InitiatePasswordRecovery::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($faker->email()));

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $result = (new AdminUserSendResetToUserMutation(
            $commandBus,
            $userFinder,
        ))($userId);

        $this->assertEquals(['userId' => $userId], $result);
    }

    public function testTooManyRequests(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(InitiatePasswordRecovery::class))
            ->andThrow(new TooManyPasswordRequestsException($faker->dateTime()));

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($faker->email()));

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(429);

        $result = (new AdminUserSendResetToUserMutation(
            $commandBus,
            $userFinder,
        ))($userId);

        $this->assertEquals(['userId' => $userId], $result);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserError::class);

        (new AdminUserSendResetToUserMutation(
            $commandBus,
            $userFinder,
        ))($userId);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\GraphQl\Mutation\User\AdminUserSendLoginLinkMutation;
use App\Model\User\Command\SendLoginLink;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

class AdminUserSendLoginLinkMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendLoginLink::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
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

        $result = (new AdminUserSendLoginLinkMutation(
            $commandBus,
            $userFinder,
        ))($userId);

        $this->assertEquals(['success' => true, 'user' => $user], $result);
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

        (new AdminUserSendLoginLinkMutation(
            $commandBus,
            $userFinder,
        ))($userId);
    }

    public function testUserNotActive(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('userId');
        $user->shouldReceive('email');

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $result = (new AdminUserSendLoginLinkMutation(
            $commandBus,
            $userFinder,
        ))($userId);

        $this->assertEquals(['success' => false, 'user' => null], $result);
    }
}

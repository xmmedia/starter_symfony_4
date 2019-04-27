<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Mutation\User\AdminUserSendResetToUserMutation;
use App\Model\Email;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserSendResetToUserMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'user' => ['userId' => $faker->uuid],
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(InitiatePasswordRecovery::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId);
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($faker->email));

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);

        $args = new Argument($data);

        $result = (new AdminUserSendResetToUserMutation(
            $commandBus,
            $userFinder
        ))($args);

        $this->assertEquals(['userId' => $data['user']['userId']], $result);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();
        $data = [
            'user' => ['userId' => $faker->uuid],
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $args = new Argument($data);

        $this->expectException(UserError::class);

        (new AdminUserSendResetToUserMutation(
            $commandBus,
            $userFinder
        ))($args);
    }
}

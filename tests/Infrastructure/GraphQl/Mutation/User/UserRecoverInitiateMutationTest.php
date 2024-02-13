<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Mutation\User\UserRecoverInitiateMutation;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use Xm\SymfonyBundle\Model\Email;

class UserRecoverInitiateMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

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
            ->andReturn(Email::fromString($data['email']));
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(\Mockery::type(Email::class))
            ->andReturn($user);

        $args = new Argument($data);

        $result = (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            true,
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTooManyRequests(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

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
            ->andReturn(Email::fromString($data['email']));
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(\Mockery::type(Email::class))
            ->andReturn($user);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(429);

        $result = (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            true,
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testValidCapitalEmail(): void
    {
        $faker = $this->faker();
        $email = strtoupper($faker->email());
        $data = [
            'email' => $email,
        ];

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
            ->andReturn(Email::fromString($data['email']));
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()->with(
                \Mockery::on(
                    function (Email $passedEmail) use ($email): bool {
                        return $passedEmail->toString() === mb_strtolower(
                            $email,
                        );
                    },
                ),
            )
            ->andReturn($user);

        $args = new Argument($data);

        $result = (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            true,
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testUserInactive(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(\Mockery::type(Email::class))
            ->andReturn($user);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            true,
        ))($args);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(\Mockery::type(Email::class))
            ->andReturnNull();

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            true,
        ))($args);
    }

    public function testInvalidEmail(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->string(3),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userFinder = \Mockery::mock(UserFinder::class);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            true,
        ))(
            $args
        );
    }
}

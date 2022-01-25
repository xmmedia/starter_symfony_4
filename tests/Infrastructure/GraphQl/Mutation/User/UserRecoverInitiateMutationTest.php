<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Mutation\User\UserRecoverInitiateMutation;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Projection\User\UserFinder;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

class UserRecoverInitiateMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(InitiatePasswordRecovery::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($data['email']));
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturn($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $result = (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            $security,
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

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(InitiatePasswordRecovery::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($data['email']));
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()->with(
                Mockery::on(
                    function (Email $passedEmail) use ($email): bool {
                        return $passedEmail->toString() === mb_strtolower(
                            $email,
                        );
                    },
                ),
            )
            ->andReturn($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $result = (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            $security,
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testUserInactive(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturn($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            $security,
        ))($args);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturnNull();

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            $security,
        ))($args);
    }

    public function testInvalidEmail(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->string(3),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userFinder = Mockery::mock(UserFinder::class);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            $security,
        ))($args);
    }

    public function testLoggedIn(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->string(5),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userFinder = Mockery::mock(UserFinder::class);

        $security = $this->createSecurity(true);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $userFinder,
            $security,
        ))($args);
    }

    private function createSecurity(bool $isGrantedResult): Security
    {
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('isGranted')
            ->once()
            ->andReturn($isGrantedResult);

        return $security;
    }
}

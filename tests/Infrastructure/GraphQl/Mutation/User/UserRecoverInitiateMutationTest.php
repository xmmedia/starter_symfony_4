<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Exception\FormValidationException;
use App\Form\User\UserRecoverInitiateType;
use App\Infrastructure\GraphQl\Mutation\User\UserRecoverInitiateMutation;
use App\Model\Email;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRecoverInitiateMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(InitiatePasswordRecovery::class))
            ->andReturn(new Envelope(new \stdClass()));

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();
        $form->shouldReceive('getData')
            ->andReturn($data);
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserRecoverInitiateType::class)
            ->andReturn($form);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId);
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

        $args = new Argument($data);

        $result = (new UserRecoverInitiateMutation(
            $commandBus,
            $formFactory,
            $userFinder
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testUserInactive(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();
        $form->shouldReceive('getData')
            ->andReturn($data);
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserRecoverInitiateType::class)
            ->andReturn($form);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturn($user);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $formFactory,
            $userFinder
        ))($args);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();
        $form->shouldReceive('getData')
            ->andReturn($data);
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserRecoverInitiateType::class)
            ->andReturn($form);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->with(Mockery::type(Email::class))
            ->andReturnNull();

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $formFactory,
            $userFinder
        ))($args);
    }

    public function testInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnFalse();
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserRecoverInitiateType::class)
            ->andReturn($form);

        $userFinder = Mockery::mock(UserFinder::class);

        $args = new Argument($data);

        $this->expectException(FormValidationException::class);

        (new UserRecoverInitiateMutation(
            $commandBus,
            $formFactory,
            $userFinder
        ))($args);
    }
}

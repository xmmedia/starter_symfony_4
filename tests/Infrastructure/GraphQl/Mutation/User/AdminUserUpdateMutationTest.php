<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\AdminUserUpdateType;
use App\Infrastructure\GraphQl\Mutation\User\AdminUserUpdateMutation;
use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Security\PasswordEncoder;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Role\Role;

class AdminUserUpdateMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'         => $faker->uuid,
            'email'          => $faker->email,
            'changePassword' => false,
            'firstName'      => $faker->name,
            'lastName'       => $faker->name,
            'role'           => 'ROLE_USER',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminUpdateUser::class))
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
            ->with(AdminUserUpdateType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserUpdateMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder
        ))($args);

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testValidChangePassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'         => $faker->uuid,
            'email'          => $faker->email,
            'changePassword' => true,
            'password'       => $faker->password,
            'firstName'      => $faker->name,
            'lastName'       => $faker->name,
            'role'           => 'ROLE_USER',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminUpdateUser::class))
            ->andReturn(new Envelope(new \stdClass()));
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminChangePassword::class))
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
            ->with(AdminUserUpdateType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::type(Role::class), $data['password'])
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserUpdateMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder
        ))(
            $args
        );

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalid(): void
    {
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
            ->with(AdminUserUpdateType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $args = new Argument([
            'user' => [],
        ]);

        $this->expectException(FormValidationException::class);

        (new AdminUserUpdateMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder
        ))($args);
    }
}

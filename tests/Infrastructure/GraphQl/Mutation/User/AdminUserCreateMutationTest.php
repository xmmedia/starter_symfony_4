<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\AdminUserCreateType;
use App\Infrastructure\GraphQl\Mutation\User\AdminUserCreateMutation;
use App\Model\User\Command\AdminCreateUser;
use App\Model\User\Token;
use App\Security\PasswordEncoder;
use App\Security\TokenGenerator;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Role\Role;

class AdminUserCreateMutationTest extends BaseTestCase
{
    public function testValidGeneratePassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid,
            'email'       => $faker->email,
            'setPassword' => false,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
            'active'      => true,
            'sendInvite'  => true,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminCreateUser::class))
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
            ->with(AdminUserCreateType::class)
            ->andReturn($form);

        $tokenGenerator = Mockery::mock(TokenGenerator::class);
        $tokenGenerator->shouldReceive('__invoke')
            ->once()
            ->andReturn(new Token('string'));

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserCreateMutation(
            $commandBus,
            $formFactory,
            $tokenGenerator,
            $passwordEncoder
        ))($args);

        $expected = [
            'userId' => $data['userId'],
            'email'  => $data['email'],
            'active' => $data['active'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testValidSetPassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid,
            'email'       => $faker->email,
            'setPassword' => true,
            'password'    => $faker->password,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
            'active'      => true,
            'sendInvite'  => true,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminCreateUser::class))
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
            ->with(AdminUserCreateType::class)
            ->andReturn($form);

        $tokenGenerator = Mockery::mock(TokenGenerator::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::type(Role::class), $data['password'])
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserCreateMutation(
            $commandBus,
            $formFactory,
            $tokenGenerator,
            $passwordEncoder
        ))($args);

        $expected = [
            'userId' => $data['userId'],
            'email'  => $data['email'],
            'active' => $data['active'],
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
            ->with(AdminUserCreateType::class)
            ->andReturn($form);

        $tokenGenerator = Mockery::mock(TokenGenerator::class);
        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $args = new Argument([
            'user' => [],
        ]);

        $this->expectException(FormValidationException::class);

        (new AdminUserCreateMutation(
            $commandBus,
            $formFactory,
            $tokenGenerator,
            $passwordEncoder
        ))($args);
    }
}

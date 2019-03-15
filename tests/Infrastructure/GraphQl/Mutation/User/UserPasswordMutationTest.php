<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\UserChangePasswordType;
use App\Infrastructure\GraphQl\Mutation\User\UserPasswordMutation;
use App\Model\User\Command\ChangePassword;
use App\Security\PasswordEncoder;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateSecurityTrait;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserPasswordMutationTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;
    use CanCreateSecurityTrait;

    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId;
        $data = [
            'currentPassword' => $faker->password(12, 250),
            'newPassword'     => $faker->password(12, 250),
            'repeatPassword'  => $faker->password(12, 250),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(ChangePassword::class))
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
            ->with(UserChangePasswordType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('userId')
            ->atLeast()
            ->times(2)
            ->andReturn($userId);
        $user->shouldReceive('roles')
            ->once()
            ->andReturn(['ROLE_USER']);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserPasswordMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $security
        ))($args);

        $expected = [
            'userId' => $userId->toString(),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password(12, 250),
            'newPassword'     => $faker->password(12, 250),
            'repeatPassword'  => $faker->password(12, 250),
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
            ->with(UserChangePasswordType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(UserInterface::class);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(FormValidationException::class);

        (new UserPasswordMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $security
        ))($args);
    }
}

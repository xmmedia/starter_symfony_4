<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Exception\FormValidationException;
use App\Form\User\UserRecoverResetType;
use App\Infrastructure\GraphQl\Mutation\User\UserRecoverResetMutation;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Command\VerifyUser;
use App\Model\User\Exception\InvalidToken;
use App\Model\User\Exception\TokenHasExpired;
use App\Model\User\Token;
use App\Security\PasswordEncoder;
use App\Security\TokenValidator;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRecoverResetMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'newPassword'    => $faker->password,
            'repeatPassword' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(VerifyUser::class))
            ->andReturn(new Envelope(new \stdClass()));
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
            ->with(UserRecoverResetType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('roles')
            ->once()
            ->andReturn(['ROLE_USER']);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'newPassword'    => $faker->password,
            'repeatPassword' => $faker->password,
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
            ->with(UserRecoverResetType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('roles')
            ->once()
            ->andReturn(['ROLE_USER']);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenExpired(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'newPassword'    => $faker->password,
            'repeatPassword' => $faker->password,
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
            ->with(UserRecoverResetType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andThrow(TokenHasExpired::before(new Token('string'), '24 hours'));

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(405);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'newPassword'    => $faker->password,
            'repeatPassword' => $faker->password,
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
            ->with(UserRecoverResetType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andThrow(InvalidToken::tokenDoesntExist(new Token('string')));

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'newPassword'    => $faker->password,
            'repeatPassword' => $faker->password,
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
            ->with(UserRecoverResetType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $tokenValidator = Mockery::mock(TokenValidator::class);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(FormValidationException::class);

        (new UserRecoverResetMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);
    }
}

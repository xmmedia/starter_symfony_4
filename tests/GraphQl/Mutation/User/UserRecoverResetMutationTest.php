<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\GraphQl\Mutation\User\UserRecoverResetMutation;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Command\VerifyUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Security\PasswordHasher;
use App\Tests\BaseTestCase;
use App\Tests\EmptyProvider;
use App\Tests\PwnedHttpClientMockTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;

class UserRecoverResetMutationTest extends BaseTestCase
{
    use EmptyProvider;
    use PwnedHttpClientMockTrait;
    use UserMockForUserMutationTrait;

    public function testNotVerified(): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(VerifyUser::class))
            ->andReturn(new Envelope(new \stdClass()));
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(ChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());

        $resetPasswordHelper = $this->getResetPasswordHelper($user);

        $requestProvider = $this->getRequestInfoProvider();

        $args = new Argument($data);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(ChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());
        $user->shouldReceive('email')
            ->once()
            ->andReturn($faker->emailVo());
        $user->shouldReceive('firstName')
            ->once()
            ->andReturn(Name::fromString($faker->firstName()));
        $user->shouldReceive('lastName')
            ->once()
            ->andReturn(Name::fromString($faker->lastName()));

        $resetPasswordHelper = $this->getResetPasswordHelper($user);
        $requestProvider = $this->getRequestInfoProvider();

        $args = new Argument($data);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenExpired(): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->with(\Mockery::type('string'))
            ->andThrow(ExpiredResetPasswordTokenException::class);

        $requestProvider = $this->getRequestInfoProvider(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(405);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->with(\Mockery::type('string'))
            ->andThrow(InvalidResetPasswordTokenException::class);

        $requestProvider = $this->getRequestInfoProvider(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        $result = (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyProvider')]
    public function testInvalidNewEmpty(?string $empty): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $empty,
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidNewTooShort(): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $faker->string(\App\Model\User\User::PASSWORD_MIN_LENGTH - 1),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidNewTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $faker->string(PasswordHasherInterface::MAX_PASSWORD_LENGTH + 1),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidCompromised(): void
    {
        $faker = $this->faker();
        $password = $faker->password();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => $password,
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $pwnedHttpClient = new MockHttpClient([
            new MockResponse(substr(strtoupper(sha1($password)), 5).':5'),
        ]);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $pwnedHttpClient,
        ))($args);
    }

    public function testInvalidNotComplex(): void
    {
        $faker = $this->faker();
        $data = [
            'token'       => $faker->password(),
            'newPassword' => '123456',
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserRecoverResetMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }
}

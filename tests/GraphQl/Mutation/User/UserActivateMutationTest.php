<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\GraphQl\Mutation\User\UserActivateMutation;
use App\Model\User\Command\ActivateUser;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Role;
use App\Security\PasswordHasher;
use App\Tests\BaseTestCase;
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
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;

class UserActivateMutationTest extends BaseTestCase
{
    use PwnedHttpClientMockTrait;
    use UserMockForUserMutationTrait;

    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(ActivateUser::class))
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
        $security = $this->createSecurity(false);
        $requestProvider = $this->getRequestInfoProvider();

        $args = new Argument($data);

        $result = (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }

    public function testLoggedIn(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $security = $this->createSecurity(true);
        $requestProvider = \Mockery::mock(RequestInfoProvider::class);

        $args = new Argument($data);

        $this->expectException(UserError::class);

        $result = (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $requestProvider = $this->getRequestInfoProvider(false);

        $user = $this->getUserMock();
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);

        $result = (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenExpired(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $faker->password(),
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

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(405);

        $result = (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $faker->password(),
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

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        $result = (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testInvalidNewEmpty(?string $empty): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $empty,
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );
    }

    public function testInvalidNewTooShort(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $faker->string(\App\Model\User\User::PASSWORD_MIN_LENGTH - 1),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );
    }

    public function testInvalidNewTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => $faker->string(PasswordHasherInterface::MAX_PASSWORD_LENGTH + 1),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
        ))(
            $args,
        );
    }

    public function testInvalidCompromised(): void
    {
        $faker = $this->faker();
        $password = $faker->password();
        $data = [
            'password' => $password,
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $security = $this->createSecurity(false);

        $pwnedHttpClient = new MockHttpClient([
            new MockResponse(substr(strtoupper(sha1($password)), 5).':5'),
        ]);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $pwnedHttpClient,
        ))(
            $args,
        );
    }

    public function testInvalidNotComplex(): void
    {
        $faker = $this->faker();
        $data = [
            'password' => '123456',
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserActivateMutation(
            $commandBus,
            $passwordHasher,
            $resetPasswordHelper,
            $security,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args,
        );
    }
}

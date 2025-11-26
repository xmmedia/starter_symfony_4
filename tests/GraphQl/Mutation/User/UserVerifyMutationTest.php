<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\GraphQl\Mutation\User\UserVerifyMutation;
use App\Model\User\Command\VerifyUser;
use App\Tests\BaseTestCase;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;

class UserVerifyMutationTest extends BaseTestCase
{
    use UserMockForUserMutationTrait;

    public function testSuccessfulVerification(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(VerifyUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturn(false);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());

        $resetPasswordHelper = $this->getResetPasswordHelper($user);
        $security = $this->createSecurity(false);
        $requestProvider = $this->getRequestInfoProvider();

        $mutation = new UserVerifyMutation(
            $commandBus,
            $resetPasswordHelper,
            $security,
            $requestProvider,
        );

        $result = $mutation();

        $this->assertEquals(['success' => true], $result);
    }

    public function testThrowsErrorWhenUserIsLoggedIn(): void
    {
        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $resetPasswordHelper = \Mockery::mock(\SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface::class);
        $security = $this->createSecurity(true); // User is logged in
        $requestProvider = \Mockery::mock(\Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider::class);

        $mutation = new UserVerifyMutation(
            $commandBus,
            $resetPasswordHelper,
            $security,
            $requestProvider,
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('Cannot activate account if logged in');
        $this->expectExceptionCode(404);

        $mutation();
    }

    public function testThrowsErrorWhenTokenIsMissing(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $resetPasswordHelper = \Mockery::mock(\SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface::class);
        $security = $this->createSecurity(false);

        // Create request provider that returns null for token
        $session = \Mockery::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with(\App\Controller\SecurityController::TOKEN_SESSION_KEY)
            ->andReturnNull();

        $request = \Mockery::mock(\Symfony\Component\HttpFoundation\Request::class);
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $requestProvider = \Mockery::mock(\Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        $mutation = new UserVerifyMutation(
            $commandBus,
            $resetPasswordHelper,
            $security,
            $requestProvider,
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('The token is invalid');
        $this->expectExceptionCode(404);

        $mutation();
    }

    public function testThrowsErrorWhenTokenIsInvalid(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $resetPasswordHelper = \Mockery::mock(\SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->andThrow(new InvalidResetPasswordTokenException());

        $security = $this->createSecurity(false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $mutation = new UserVerifyMutation(
            $commandBus,
            $resetPasswordHelper,
            $security,
            $requestProvider,
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('The token is invalid');
        $this->expectExceptionCode(404);

        $mutation();
    }

    public function testThrowsErrorWhenTokenIsExpired(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $resetPasswordHelper = \Mockery::mock(\SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->andThrow(new ExpiredResetPasswordTokenException());

        $security = $this->createSecurity(false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $mutation = new UserVerifyMutation(
            $commandBus,
            $resetPasswordHelper,
            $security,
            $requestProvider,
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('The link has expired');
        $this->expectExceptionCode(405);

        $mutation();
    }

    public function testThrowsErrorWhenUserAlreadyVerified(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturn(true);

        $resetPasswordHelper = $this->getResetPasswordHelper($user, false);
        $security = $this->createSecurity(false);
        $requestProvider = $this->getRequestInfoProvider(false);

        $mutation = new UserVerifyMutation(
            $commandBus,
            $resetPasswordHelper,
            $security,
            $requestProvider,
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('Your account has already been activated');
        $this->expectExceptionCode(404);

        $mutation();
    }
}

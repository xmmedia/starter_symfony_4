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
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;

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

        $mutation = new UserVerifyMutation(
            $commandBus,
            $this->getResetPasswordHelper($user),
            $this->createSecurity(false),
            $this->getRequestInfoProvider(),
        );

        $this->assertEquals(['success' => true], $mutation());
    }

    public function testThrowsErrorWhenUserIsLoggedIn(): void
    {
        $mutation = new UserVerifyMutation(
            \Mockery::mock(MessageBusInterface::class),
            \Mockery::mock(ResetPasswordHelperInterface::class),
            $this->createSecurity(true), // User is logged in
            \Mockery::mock(RequestInfoProvider::class),
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('Cannot activate account if logged in');
        $this->expectExceptionCode(404);

        $mutation();
    }

    public function testThrowsErrorWhenTokenIsMissing(): void
    {
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

        $requestProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        $mutation = new UserVerifyMutation(
            \Mockery::mock(MessageBusInterface::class),
            \Mockery::mock(ResetPasswordHelperInterface::class),
            $this->createSecurity(false),
            $requestProvider,
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('The token is invalid');
        $this->expectExceptionCode(404);

        $mutation();
    }

    public function testThrowsErrorWhenTokenIsInvalid(): void
    {
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->andThrow(new InvalidResetPasswordTokenException());

        $mutation = new UserVerifyMutation(
            \Mockery::mock(MessageBusInterface::class),
            $resetPasswordHelper,
            $this->createSecurity(false),
            $this->getRequestInfoProvider(false),
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('The token is invalid');
        $this->expectExceptionCode(404);

        $mutation();
    }

    public function testThrowsErrorWhenTokenIsExpired(): void
    {
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->andThrow(new ExpiredResetPasswordTokenException());

        $mutation = new UserVerifyMutation(
            \Mockery::mock(MessageBusInterface::class),
            $resetPasswordHelper,
            $this->createSecurity(false),
            $this->getRequestInfoProvider(false),
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('The link has expired');
        $this->expectExceptionCode(405);

        $mutation();
    }

    public function testThrowsErrorWhenUserAlreadyVerified(): void
    {
        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();

        $mutation = new UserVerifyMutation(
            $commandBus,
            $this->getResetPasswordHelper($user, false),
            $this->createSecurity(false),
            $this->getRequestInfoProvider(false),
        );

        $this->expectException(UserError::class);
        $this->expectExceptionMessage('Your account has already been activated');
        $this->expectExceptionCode(404);

        $mutation();
    }
}

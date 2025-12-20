<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\User;

use App\Controller\SecurityController;
use App\Entity\User;
use App\GraphQl\Query\User\UserRecoverResetPasswordStrengthQuery;
use App\Model\User\Name;
use App\Tests\BaseTestCase;
use App\Tests\PwnedHttpClientMockTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;

class UserRecoverResetPasswordStrengthQueryTest extends BaseTestCase
{
    use PwnedHttpClientMockTrait;

    public function testAllowed(): void
    {
        $faker = $this->faker();
        $token = $faker->uuid();
        $password = $faker->password();
        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('email')
            ->once()
            ->andReturn($email);
        $user->shouldReceive('firstName')
            ->once()
            ->andReturn($firstName);
        $user->shouldReceive('lastName')
            ->once()
            ->andReturn($lastName);

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with(SecurityController::TOKEN_SESSION_KEY)
            ->andReturn($token);

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $requestProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->with($token)
            ->andReturn($user);

        $query = new UserRecoverResetPasswordStrengthQuery(
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        );

        $result = $query($password);

        $this->assertEquals(['allowed' => true], $result);
    }

    public function testPasswordNotAllowedWhenPwned(): void
    {
        $faker = $this->faker();
        $token = $faker->uuid();
        $password = $faker->password();
        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('email')
            ->once()
            ->andReturn($email);
        $user->shouldReceive('firstName')
            ->once()
            ->andReturn($firstName);
        $user->shouldReceive('lastName')
            ->once()
            ->andReturn($lastName);

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with(SecurityController::TOKEN_SESSION_KEY)
            ->andReturn($token);

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $requestProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->with($token)
            ->andReturn($user);

        $pwnedHttpClient = new MockHttpClient([
            new MockResponse(substr(strtoupper(sha1($password)), 5).':5'),
        ]);

        $query = new UserRecoverResetPasswordStrengthQuery(
            $resetPasswordHelper,
            $requestProvider,
            new PasswordStrengthFake(),
            $pwnedHttpClient,
        );

        $result = $query($password);

        $this->assertEquals(['allowed' => false], $result);
    }

    public function testPasswordAllowedWhenNoTokenInSession(): void
    {
        $faker = $this->faker();
        $password = $faker->password();

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with(SecurityController::TOKEN_SESSION_KEY)
            ->andReturnNull();

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $requestProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        $query = new UserRecoverResetPasswordStrengthQuery(
            \Mockery::mock(ResetPasswordHelperInterface::class),
            $requestProvider,
        );

        $result = $query($password);

        $this->assertEquals(['allowed' => true], $result);
    }

    public function testPasswordAllowedWhenTokenExpired(): void
    {
        $faker = $this->faker();
        $token = $faker->uuid();

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with(SecurityController::TOKEN_SESSION_KEY)
            ->andReturn($token);

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $requestProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->with($token)
            ->andThrow(ExpiredResetPasswordTokenException::class);

        $query = new UserRecoverResetPasswordStrengthQuery($resetPasswordHelper, $requestProvider);

        $result = $query($faker->password());

        $this->assertEquals(['allowed' => true], $result);
    }

    public function testPasswordAllowedWhenTokenInvalid(): void
    {
        $faker = $this->faker();
        $token = $faker->uuid();

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with(SecurityController::TOKEN_SESSION_KEY)
            ->andReturn($token);

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $requestProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn($request);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('validateTokenAndFetchUser')
            ->once()
            ->with($token)
            ->andThrow(InvalidResetPasswordTokenException::class);

        $query = new UserRecoverResetPasswordStrengthQuery($resetPasswordHelper, $requestProvider);

        $result = $query($faker->password());

        $this->assertEquals(['allowed' => true], $result);
    }
}

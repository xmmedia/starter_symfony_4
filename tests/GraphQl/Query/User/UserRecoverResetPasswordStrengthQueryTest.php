<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\User;

use App\Controller\SecurityController;
use App\Entity\User;
use App\GraphQl\Query\User\UserRecoverResetPasswordStrengthQuery;
use App\Model\User\Name;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

class UserRecoverResetPasswordStrengthQueryTest extends BaseTestCase
{
    public function testPasswordAllowedWithValidToken(): void
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

        $passwordStrength = \Mockery::mock(PasswordStrengthInterface::class);
        $passwordStrength->shouldReceive('__invoke')
            ->once()
            ->andReturn(['valid' => true, 'score' => 100]);

        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getContent')
            ->once()
            ->andReturn("ABC123:2\nDEF456:1");  // Return pwned hashes but with count < 3

        $pwnedHttpClient = \Mockery::mock(HttpClientInterface::class);
        $pwnedHttpClient->shouldReceive('request')
            ->once()
            ->andReturn($response);

        $query = new UserRecoverResetPasswordStrengthQuery(
            $resetPasswordHelper,
            $requestProvider,
            $passwordStrength,
            $pwnedHttpClient,
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

        $passwordStrength = \Mockery::mock(PasswordStrengthInterface::class);
        $passwordStrength->shouldReceive('__invoke')
            ->once()
            ->andReturn(['valid' => true, 'score' => 3]);  // Score > 2 to pass complexity check

        $hash = strtoupper(sha1($password));
        $hashSuffix = substr($hash, 5);  // Get the suffix after the first 5 chars

        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getContent')
            ->once()
            ->andReturn($hashSuffix.':12345');  // Return matching hash with count >= 3

        $pwnedHttpClient = \Mockery::mock(HttpClientInterface::class);
        $pwnedHttpClient->shouldReceive('request')
            ->once()
            ->andReturn($response);

        $query = new UserRecoverResetPasswordStrengthQuery(
            $resetPasswordHelper,
            $requestProvider,
            $passwordStrength,
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

        $query = new UserRecoverResetPasswordStrengthQuery(
            $resetPasswordHelper,
            $requestProvider,
        );

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

        $query = new UserRecoverResetPasswordStrengthQuery(
            $resetPasswordHelper,
            $requestProvider,
        );

        $result = $query($faker->password());

        $this->assertEquals(['allowed' => true], $result);
    }

    public function testPasswordAllowedWithoutPwnedCheck(): void
    {
        $faker = $this->faker();
        $token = $faker->uuid();
        // Use a strong unique password that won't be in pwned database
        $password = 'V@lid&P@ssw0rd!'.uniqid().uniqid();
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

        // No password strength or pwned client provided - will use defaults
        $query = new UserRecoverResetPasswordStrengthQuery(
            $resetPasswordHelper,
            $requestProvider,
        );

        $result = $query($password);

        $this->assertEquals(['allowed' => true], $result);
    }
}

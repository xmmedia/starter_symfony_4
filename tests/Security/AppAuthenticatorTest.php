<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Model\User\Credentials;
use App\Security\AppAuthenticator;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;

class AppAuthenticatorTest extends BaseTestCase
{
    /** @var Request */
    private $requestWithoutSession;
    /** @var Request */
    private $requestWithSession;
    /** @var AppAuthenticator */
    private $authenticator;
    /** @var RouterInterface|\Mockery\MockInterface */
    private $router;
    /** @var UserPasswordHasherInterface|\Mockery\MockInterface */
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->requestWithoutSession = new Request([], [], [], [], [], []);
        $this->requestWithSession = new Request([], [], [], [], [], []);

        $session = Mockery::mock(SessionInterface::class);
        $this->requestWithSession->setSession($session);

        $this->router = Mockery::mock(RouterInterface::class);
        $this->passwordHasher = Mockery::mock(UserPasswordHasherInterface::class);

        $this->authenticator = new AppAuthenticator(
            $this->router,
            $this->passwordHasher
        );
    }

    public function testSupports(): void
    {
        $this->requestWithSession->attributes->add(['_route' => 'app_login']);
        $this->requestWithSession->setMethod('POST');

        $this->assertTrue($this->authenticator->supports($this->requestWithSession));
    }

    public function testStartRequestToLogin(): void
    {
        $this->requestWithoutSession->attributes->add(['_route' => 'app_login']);

        $this->router->shouldReceive('generate')
            ->with('app_login')
            ->once()
            ->andReturn('/login');

        $res = $this->authenticator->start($this->requestWithoutSession);

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertEquals('/login', $res->getTargetUrl());
    }

    public function testGetCredentials(): void
    {
        $faker = $this->faker();

        $email = $faker->email();
        $password = $faker->password();

        $this->requestWithSession->request->add([
            'email'    => $email,
            'password' => $password,
        ]);

        $this->requestWithSession->getSession()
            ->shouldReceive('set')
            ->with(Security::LAST_USERNAME, $email)
            ->once();

        $credentials = $this->authenticator->getCredentials($this->requestWithSession);

        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertSame($email, $credentials->email());
        $this->assertSame($password, $credentials->password());
    }

    public function testGetCredentialsNulls(): void
    {
        $this->requestWithSession->request->add([
            '_email'      => null,
            '_password'   => null,
        ]);

        $this->requestWithSession->getSession()
            ->shouldReceive('set')
            ->with(Security::LAST_USERNAME, null)
            ->once();

        $credentials = $this->authenticator->getCredentials($this->requestWithSession);

        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertNull($credentials->email());
        $this->assertNull($credentials->password());
    }

    public function testGetCredentialsNotInRequest(): void
    {
        $this->requestWithSession
            ->getSession()
            ->shouldReceive('set')
            ->with(Security::LAST_USERNAME, null)
            ->once();

        $credentials = $this->authenticator->getCredentials($this->requestWithSession);

        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertNull($credentials->email());
        $this->assertNull($credentials->password());
    }

    public function testGetUser(): void
    {
        $faker = $this->faker();

        $email = $faker->email();
        $password = $faker->password();
        $credentials = Credentials::build($email, $password);

        $user = Mockery::mock(User::class);

        $userProvider = Mockery::mock(EntityUserProvider::class);
        $userProvider->shouldReceive('loadUserByIdentifier')
            ->with($credentials->email())
            ->once()
            ->andReturn($user);

        /** @var User $userReceived */
        $userReceived = $this->authenticator->getUser($credentials, $userProvider);

        $this->assertInstanceOf(User::class, $userReceived);
    }

    public function testGetUserInvalidEmail(): void
    {
        $email = 'email';
        $credentials = Credentials::build($email, null);

        $userProvider = Mockery::mock(EntityUserProvider::class);
        $userProvider->shouldReceive('loadUserByIdentifier')
            ->with($credentials->email())
            ->once()
            ->andThrow(new UserNotFoundException());

        $this->expectException(CustomUserMessageAuthenticationException::class);

        $this->authenticator->getUser($credentials, $userProvider);
    }

    public function testCheckCredentials(): void
    {
        $this->passwordHasher->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $faker = $this->faker();

        $email = $faker->email();
        $password = $faker->password();
        $credentials = Credentials::build($email, $password);

        $user = Mockery::mock(User::class);

        $result = $this->authenticator->checkCredentials($credentials, $user);

        $this->assertTrue($result);
    }

    public function testCheckCredentialsFalse(): void
    {
        $this->passwordHasher->shouldReceive('isPasswordValid')
            ->andReturnFalse();

        $faker = $this->faker();

        $email = $faker->email();
        $password = $faker->password();
        $credentials = Credentials::build($email, $password);

        $user = Mockery::mock(User::class);

        $result = $this->authenticator->checkCredentials($credentials, $user);

        $this->assertFalse($result);
    }

    public function testCheckCredentialsEmptyPassword(): void
    {
        $this->passwordHasher->shouldReceive('isPasswordValid')
            ->andReturnFalse();

        $faker = $this->faker();

        $email = $faker->email();
        $credentials = Credentials::build($email, '');

        $user = Mockery::mock(User::class);

        $result = $this->authenticator->checkCredentials($credentials, $user);

        $this->assertFalse($result);
    }

    public function testCheckCredentialsNullPassword(): void
    {
        $this->passwordHasher->shouldReceive('isPasswordValid')
            ->andReturnFalse();

        $faker = $this->faker();

        $email = $faker->email();
        $credentials = Credentials::build($email, '');

        $user = Mockery::mock(User::class);

        $result = $this->authenticator->checkCredentials($credentials, $user);

        $this->assertFalse($result);
    }

    public function testCheckCredentialsShortPassword(): void
    {
        $this->passwordHasher->shouldReceive('isPasswordValid')
            ->andReturnFalse();

        $faker = $this->faker();

        $email = $faker->email();
        $credentials = Credentials::build($email, 'a');

        $user = Mockery::mock(User::class);

        $result = $this->authenticator->checkCredentials($credentials, $user);

        $this->assertFalse($result);
    }

    public function testGetPassword(): void
    {
        $faker = $this->faker();

        $password = $faker->password();
        $credentials = Credentials::build($faker->email(), $password);

        $result = $this->authenticator->getPassword($credentials);

        $this->assertEquals($password, $result);
    }

    public function testGetPasswordNotCredentials(): void
    {
        $faker = $this->faker();

        $this->assertNull(
            $this->authenticator->getPassword(['password' => $faker->password()])
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        $providerKey = 'key';
        $this->requestWithSession
            ->getSession()
            ->shouldReceive('get')
            ->with('_security.'.$providerKey.'.target_path')
            ->andReturnNull();
        $token = Mockery::mock(TokenInterface::class);

        $result = $this->authenticator->onAuthenticationSuccess(
            $this->requestWithSession,
            $token,
            $providerKey
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('/admin', $result->getTargetUrl());
    }

    public function testOnAuthenticationSuccessWithTarget(): void
    {
        $providerKey = 'key';
        $this->requestWithSession
            ->getSession()
            ->shouldReceive('get')
            ->with('_security.'.$providerKey.'.target_path')
            ->andReturn('/go');
        $token = Mockery::mock(TokenInterface::class);

        $result = $this->authenticator->onAuthenticationSuccess(
            $this->requestWithSession,
            $token,
            $providerKey
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('/go', $result->getTargetUrl());
    }

    public function testRememberMe()
    {
        $doSupport = $this->authenticator->supportsRememberMe();

        $this->assertTrue($doSupport);
    }
}

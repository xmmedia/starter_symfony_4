<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Model\User\Command\ActivateUser;
use App\Security\UserChecker;
use App\Tests\BaseTestCase;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Security\Exception\AccountInactiveException;
use Xm\SymfonyBundle\Security\Exception\AccountNotVerifiedException;

class UserCheckerTest extends BaseTestCase
{
    public function testCheckPreAuth(): void
    {
        $faker = $this->faker();

        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestInfoProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn(\Mockery::mock(Request::class));

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('checkRequestPath')
            ->once()
            ->andReturnTrue();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(ActivateUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $doctrine = \Mockery::mock(ManagerRegistry::class);
        $doctrine->shouldReceive('getManagerForClass')
            ->once()
            ->andReturnSelf();
        $doctrine->shouldReceive('refresh')->once();

        $checker = new UserChecker($requestInfoProvider, $httpUtils, $commandBus, $doctrine);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthAlreadyVerified(): void
    {
        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestInfoProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn(\Mockery::mock(Request::class));

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('checkRequestPath')
            ->once()
            ->andReturnTrue();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $doctrine = \Mockery::mock(ManagerRegistry::class);

        $checker = new UserChecker($requestInfoProvider, $httpUtils, $commandBus, $doctrine);

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthNotLoginLinkRouter(): void
    {
        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestInfoProvider->shouldReceive('currentRequest')
            ->once()
            ->andReturn(\Mockery::mock(Request::class));

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('checkRequestPath')
            ->once()
            ->andReturnFalse();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $doctrine = \Mockery::mock(ManagerRegistry::class);

        $checker = new UserChecker($requestInfoProvider, $httpUtils, $commandBus, $doctrine);

        $user = \Mockery::mock(User::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthNotUserEntity(): void
    {
        $checker = $this->getUserChecker();

        $user = \Mockery::mock(UserInterface::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPostAuthValid(): void
    {
        $checker = $this->getUserChecker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthNotVerified(): void
    {
        $checker = $this->getUserChecker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnfalse();
        $user->shouldNotReceive('active');

        $this->expectException(AccountNotVerifiedException::class);

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthNotActive(): void
    {
        $checker = $this->getUserChecker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();

        $this->expectException(AccountInactiveException::class);

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthNotUserEntity(): void
    {
        $checker = $this->getUserChecker();

        $user = \Mockery::mock(UserInterface::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPostAuth($user);
    }

    private function getUserChecker(): UserChecker
    {
        return new UserChecker(
            \Mockery::mock(RequestInfoProvider::class),
            \Mockery::mock(HttpUtils::class),
            \Mockery::mock(MessageBusInterface::class),
            \Mockery::mock(ManagerRegistry::class),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\DataProvider\IssuerProvider;
use App\Entity\User;
use App\Tests\BaseTestCase;
use Mockery;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class IssuerProviderTest extends BaseTestCase
{
    public function testLoggedIn(): void
    {
        $userId = Uuid::uuid4();

        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('userId');
        $property->setAccessible(true);
        $property->setValue($user, $userId);

        $provider = new IssuerProvider($this->createSecurity($user));

        $this->assertEquals($userId->toString(), $provider->getIssuer());
    }

    public function testNotLoggedIn(): void
    {
        $provider = new IssuerProvider($this->createSecurity(null));

        $this->assertEquals('anonymous', $provider->getIssuer());
    }

    public function testCli(): void
    {
        $tokenStorage = Mockery::mock(TokenStorageInterface::class);
        $tokenStorage->shouldReceive('getToken')
            ->andReturnNull();

        $container = $this->createContainer('security.token_storage', $tokenStorage);

        $security = new Security($container);

        $provider = new IssuerProvider($security);

        $this->assertEquals('cli', $provider->getIssuer());
    }

    /**
     * $user: false = no token storage within container, null = no user.
     *
     * @param UserInterface|bool|null $user
     */
    private function createSecurity($user): Security
    {
        $tokenStorage = Mockery::mock(TokenStorageInterface::class);

        if (false !== $user) {
            $token = Mockery::mock(TokenInterface::class);
            $token->shouldReceive('getUser')
                ->andReturn($user)
            ;

            $tokenStorage->shouldReceive('getToken')
                ->andReturn($token)
            ;
        }

        $container = $this->createContainer('security.token_storage', $tokenStorage);

        return new Security($container);
    }

    private function createContainer($serviceId, $serviceObject): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with($serviceId)
            ->andReturn($serviceObject);

        return $container;
    }
}

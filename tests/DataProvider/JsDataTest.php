<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\DataProvider\JsData;
use App\Entity\User;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class JsDataTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var RouterInterface|\Symfony\Bundle\FrameworkBundle\Routing\Router|Mockery\Mock */
    private $router;

    /** @var CsrfTokenManagerInterface|Mockery\Mock */
    private $tokenManager;

    public function setUp(): void
    {
        $this->router = Mockery::mock(RouterInterface::class);
        $this->tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);
    }

    public function testGetPublic(): void
    {
        $class = $this->getJsData(null);

        $this->assertSame([], $class->get('public'));
    }

    public function testGetAdmin(): void
    {
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $firstName = $faker->name;
        $lastName = $faker->name;

        $token = Mockery::mock(CsrfToken::class);
        $token->shouldReceive('getValue')
            ->once()
            ->andReturn('csrf_token');

        $this->tokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user, $email);
        $property = $reflection->getProperty('firstName');
        $property->setAccessible(true);
        $property->setValue($user, $firstName);
        $property = $reflection->getProperty('lastName');
        $property->setAccessible(true);
        $property->setValue($user, $lastName);

        $class = $this->getJsData($user);

        $expected = [
            'user' => [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
            ],
            'csrfToken' => 'csrf_token',
        ];

        $this->assertSame($expected, $class->get('admin'));
    }

    private function getJsData(?UserInterface $user): JsData
    {
        return new JsData(
            $this->router,
            $this->createSecurity($user),
            $this->tokenManager
        );
    }

    private function createSecurity(?UserInterface $user): Security
    {
        $tokenStorage = Mockery::mock(TokenStorageInterface::class);

        if (null !== $user) {
            $token = Mockery::mock(TokenInterface::class);
            $token->shouldReceive('getUser')
                ->once()
                ->andReturn($user)
            ;

            $tokenStorage->shouldReceive('getToken')
                ->once()
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

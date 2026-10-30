<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\User;
use App\EventListener\ImpersonationListener;
use App\Model\Auth\Command\UserEndedImpersonating;
use App\Model\Auth\Command\UserStartedImpersonating;
use App\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Xm\SymfonyBundle\Model\Email;

class ImpersonationListenerTest extends BaseTestCase
{
    use EventListenerAttributeTrait;

    public function testListensTo(): void
    {
        $this->assertSame(
            SwitchUserEvent::class,
            $this->eventListenerAttribute(ImpersonationListener::class, 'onSwitchUser')->event,
        );
    }

    public function testStart(): void
    {
        $faker = $this->faker();
        $email = $faker->email();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(UserStartedImpersonating::class))
            ->andReturn(new Envelope(new \stdClass()));

        $adminUser = \Mockery::mock(User::class);
        $adminUser->shouldReceive('userId')
            ->andReturn($faker->userId());

        $impersonatedUser = \Mockery::mock(User::class);
        $impersonatedUser->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $impersonatedUser->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($email));

        $originalToken = \Mockery::mock(TokenInterface::class);
        $originalToken->shouldReceive('getUser')
            ->once()
            ->andReturn($adminUser);

        $token = \Mockery::mock(SwitchUserToken::class);
        $token->shouldReceive('getOriginalToken')
            ->once()
            ->andReturn($originalToken);

        $request = Request::create(
            '',
            Request::METHOD_GET,
            ['_switch_user' => $email],
            [],
            [],
            ['REMOTE_ADDR' => $faker->ipv4()],
        );
        $request->attributes->set('_route', $faker->slug());

        $event = new SwitchUserEvent($request, $impersonatedUser, $token);

        $tokenStorage = \Mockery::mock(TokenStorageInterface::class);

        new ImpersonationListener($commandBus, $tokenStorage)->onSwitchUser($event);
    }

    #[DataProvider('exitProvider')]
    public function testExit(?string $switchUser): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(UserEndedImpersonating::class))
            ->andReturn(new Envelope(new \stdClass()));

        $adminUser = \Mockery::mock(User::class);
        $adminUser->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());

        $impersonatedUser = \Mockery::mock(User::class);
        $impersonatedUser->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());

        $currentToken = \Mockery::mock(SwitchUserToken::class);
        $currentToken->shouldReceive('getUser')
            ->once()
            ->andReturn($impersonatedUser);

        $tokenStorage = \Mockery::mock(TokenStorageInterface::class);
        $tokenStorage->shouldReceive('getToken')
            ->once()
            ->andReturn($currentToken);

        $originalToken = \Mockery::mock(TokenInterface::class);

        $request = Request::create(
            '',
            Request::METHOD_GET,
            ['_switch_user' => $switchUser ?? $faker->email()],
            [],
            [],
            ['REMOTE_ADDR' => $faker->ipv4()],
        );
        $request->attributes->set('_route', $faker->slug());

        $event = new SwitchUserEvent($request, $adminUser, $originalToken);

        new ImpersonationListener($commandBus, $tokenStorage)->onSwitchUser($event);
    }

    /**
     * Switching to another user while impersonating exits first, with the new user in the request.
     */
    public static function exitProvider(): \Generator
    {
        yield 'exit' => ['_exit'];
        yield 'switching to another user' => [null];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\User;
use App\EventListener\LoginLoggerListener;
use App\Model\Auth\Command\UserLoggedInSuccessfully;
use App\Model\Auth\Command\UserLoginFailed;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Xm\SymfonyBundle\Model\Email;

class LoginLoggerListenerTest extends BaseTestCase
{
    use EventListenerAttributeTrait;

    public function testListensTo(): void
    {
        $this->assertSame(
            InteractiveLoginEvent::class,
            $this->eventListenerAttribute(LoginLoggerListener::class, 'loginSuccess')->event,
        );
        $this->assertSame(
            LoginFailureEvent::class,
            $this->eventListenerAttribute(LoginLoggerListener::class, 'loginFailure')->event,
        );
    }

    public function testSuccessfulLogin(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(UserLoggedInSuccessfully::class))
            ->andReturn(new Envelope(new \stdClass()));

        $request = Request::create(
            '',
            Request::METHOD_GET,
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => $faker->ipv4(),
            ],
        );
        $request->attributes->set('_route', $faker->slug());

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString('test@example.com'));

        $token = \Mockery::mock(TokenInterface::class);
        $token->shouldReceive('getUser')
            ->andReturn($user);

        $event = new InteractiveLoginEvent($request, $token);

        new LoginLoggerListener($commandBus)->loginSuccess($event);
    }

    public function testFailedLogin(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(UserLoginFailed::class))
            ->andReturn(new Envelope(new \stdClass()));

        $request = Request::create(
            '',
            Request::METHOD_GET,
            [],
            [],
            [],
            [
                'REMOTE_ADDR'     => $faker->ipv4(),
                'HTTP_USER_AGENT' => $faker->userAgent(),
            ],
        );
        $request->attributes->set('_route', $faker->slug());

        $authenticator = \Mockery::mock(AuthenticatorInterface::class);

        $event = new LoginFailureEvent(
            new AuthenticationException(),
            $authenticator,
            $request,
            null,
            'main',
        );

        new LoginLoggerListener($commandBus)->loginFailure($event);
    }
}

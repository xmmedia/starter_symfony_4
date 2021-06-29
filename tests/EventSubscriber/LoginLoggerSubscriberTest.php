<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Entity\User;
use App\EventSubscriber\LoginLoggerSubscriber;
use App\Model\Auth\Command\UserLoggedInSuccessfully;
use App\Model\Auth\Command\UserLoginFailed;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Xm\SymfonyBundle\Model\Email;

class LoginLoggerSubscriberTest extends BaseTestCase
{
    public function testSubscribedEvents(): void
    {
        $subscribed = LoginLoggerSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(InteractiveLoginEvent::class, $subscribed);
        $this->assertArrayHasKey(LoginFailureEvent::class, $subscribed);
    }

    public function testSuccessfulLogin(): void
    {
        $faker = $this->faker();

        $method = LoginLoggerSubscriber::getSubscribedEvents()[InteractiveLoginEvent::class];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->with(Mockery::type(UserLoggedInSuccessfully::class))
            ->andReturn(new Envelope(new \stdClass()));

        $request = Request::create(
            '',
            'GET',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => $faker->ipv4(),
            ]
        );

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->andReturn(Email::fromString('test@example.com'));

        $token = Mockery::mock(TokenInterface::class);
        $token->shouldReceive('getUser')
            ->andReturn($user);

        $event = new InteractiveLoginEvent($request, $token);

        $listener = new LoginLoggerSubscriber($commandBus);

        $listener->{$method}($event);
    }

    public function testFailedLogin(): void
    {
        $faker = $this->faker();

        $method = LoginLoggerSubscriber::getSubscribedEvents()[LoginFailureEvent::class];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->with(Mockery::type(UserLoginFailed::class))
            ->andReturn(new Envelope(new \stdClass()));

        $request = Request::create(
            '',
            'GET',
            [],
            [],
            [],
            [
                'REMOTE_ADDR'     => $faker->ipv4(),
                'HTTP_USER_AGENT' => $faker->userAgent(),
            ]
        );

        $authenticator = Mockery::mock(AuthenticatorInterface::class);

        $event = new LoginFailureEvent(
            new AuthenticationException(),
            $authenticator,
            $request,
            null,
            'main'
        );

        $listener = new LoginLoggerSubscriber($commandBus);

        $listener->{$method}($event);
    }
}

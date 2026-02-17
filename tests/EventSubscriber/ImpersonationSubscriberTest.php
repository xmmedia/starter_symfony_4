<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Entity\User;
use App\EventSubscriber\ImpersonationSubscriber;
use App\Model\Auth\Command\UserEndedImpersonating;
use App\Model\Auth\Command\UserStartedImpersonating;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Xm\SymfonyBundle\Model\Email;

class ImpersonationSubscriberTest extends BaseTestCase
{
    public function testSubscribedEvents(): void
    {
        $subscribed = ImpersonationSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(SwitchUserEvent::class, $subscribed);
    }

    public function testStart(): void
    {
        $faker = $this->faker();
        $email = $faker->email();

        $method = ImpersonationSubscriber::getSubscribedEvents()[SwitchUserEvent::class];

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

        $subscriber = new ImpersonationSubscriber($commandBus);
        $subscriber->{$method}($event);
    }

    public function testExit(): void
    {
        $faker = $this->faker();

        $method = ImpersonationSubscriber::getSubscribedEvents()[SwitchUserEvent::class];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(UserEndedImpersonating::class))
            ->andReturn(new Envelope(new \stdClass()));

        $adminUser = \Mockery::mock(User::class);
        $adminUser->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());

        $originalToken = \Mockery::mock(TokenInterface::class);

        $request = Request::create(
            '',
            Request::METHOD_GET,
            ['_switch_user' => '_exit'],
            [],
            [],
            ['REMOTE_ADDR' => $faker->ipv4()],
        );
        $request->attributes->set('_route', $faker->slug());

        $event = new SwitchUserEvent($request, $adminUser, $originalToken);

        $subscriber = new ImpersonationSubscriber($commandBus);
        $subscriber->{$method}($event);
    }
}

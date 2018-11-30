<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\Auth\AuthId;
use App\Model\Auth\Event\UserLoggedIn;
use App\Model\Email;
use App\Model\User\UserId;
use App\ProcessManager\UserLoggedInProcessManager;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserLoggedInProcessManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSend(): void
    {
        $faker = Faker\Factory::create();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(\App\Model\User\Command\UserLoggedIn::class))
            ->andReturn(new Envelope(new \StdClass()));

        $event = UserLoggedIn::now(
            AuthId::generate(),
            UserId::generate(),
            Email::fromString($faker->email),
            $faker->userAgent,
            $faker->ipv4
        );

        (new UserLoggedInProcessManager($commandBus))($event);
    }
}

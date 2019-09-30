<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\ProcessManager\UserLoggedInProcessManager;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Auth\Event\UserLoggedIn;

class UserLoggedInProcessManagerTest extends BaseTestCase
{
    public function testSend(): void
    {
        $faker = $this->faker();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(\Xm\SymfonyBundle\Model\User\Command\UserLoggedIn::class))
            ->andReturn(new Envelope(new \stdClass()));

        $event = UserLoggedIn::now(
            $faker->authId,
            $faker->userId,
            $faker->emailVo,
            $faker->userAgent,
            $faker->ipv4
        );

        (new UserLoggedInProcessManager($commandBus))($event);
    }
}

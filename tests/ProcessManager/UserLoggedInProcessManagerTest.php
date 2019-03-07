<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\Auth\Event\UserLoggedIn;
use App\ProcessManager\UserLoggedInProcessManager;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserLoggedInProcessManagerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function testSend(): void
    {
        $faker = $this->faker();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(\App\Model\User\Command\UserLoggedIn::class))
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

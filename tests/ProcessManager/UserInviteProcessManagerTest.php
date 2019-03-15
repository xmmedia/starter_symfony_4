<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Command\SendActivation;
use App\Model\User\Event\UserWasCreatedByAdmin;
use App\Model\User\Name;
use App\ProcessManager\UserInviteProcessManager;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Role\Role;

class UserInviteProcessManagerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function testSend(): void
    {
        $faker = $this->faker();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(SendActivation::class))
            ->andReturn(new Envelope(new \stdClass()));

        $event = UserWasCreatedByAdmin::now(
            $faker->userId,
            $faker->emailVo,
            $faker->password(12, 250),
            new Role('ROLE_USER'),
            true,
            Name::fromString($faker->firstName),
            Name::fromString($faker->lastName),
            true
        );

        (new UserInviteProcessManager($commandBus))($event);
    }

    public function testDontSend(): void
    {
        $faker = $this->faker();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $event = UserWasCreatedByAdmin::now(
            $faker->userId,
            $faker->emailVo,
            $faker->password(12, 250),
            new Role('ROLE_USER'),
            true,
            Name::fromString($faker->firstName),
            Name::fromString($faker->lastName),
            false
        );

        (new UserInviteProcessManager($commandBus))($event);
    }
}

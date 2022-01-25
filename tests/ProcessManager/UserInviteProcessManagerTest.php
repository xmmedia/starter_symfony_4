<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Command\SendActivation;
use App\Model\User\Event\UserWasAddedByAdmin;
use App\Model\User\Name;
use App\Model\User\Role;
use App\ProcessManager\UserInviteProcessManager;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserInviteProcessManagerTest extends BaseTestCase
{
    public function testSend(): void
    {
        $faker = $this->faker();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(SendActivation::class))
            ->andReturn(new Envelope(new \stdClass()));

        $event = UserWasAddedByAdmin::now(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            true,
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            true,
        );

        (new UserInviteProcessManager($commandBus))($event);
    }

    public function testDontSend(): void
    {
        $faker = $this->faker();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $event = UserWasAddedByAdmin::now(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            true,
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            false,
        );

        (new UserInviteProcessManager($commandBus))($event);
    }
}

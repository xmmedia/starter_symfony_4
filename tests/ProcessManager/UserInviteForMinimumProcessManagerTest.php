<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Command\SendActivation;
use App\Model\User\Event\MinimalUserWasAddedByAdmin;
use App\Model\User\Name;
use App\Model\User\Role;
use App\ProcessManager\UserInviteForMinimumProcessManager;
use App\Tests\BaseTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserInviteForMinimumProcessManagerTest extends BaseTestCase
{
    public function testSendsActivationWhenSendInviteIsTrue(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendActivation::class))
            ->andReturn(new Envelope(new \stdClass()));

        $event = MinimalUserWasAddedByAdmin::now(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            true, // sendInvite = true
        );

        $processManager = new UserInviteForMinimumProcessManager($commandBus);
        $processManager($event);
    }

    public function testDoesNotSendActivationWhenSendInviteIsFalse(): void
    {
        $faker = $this->faker();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $event = MinimalUserWasAddedByAdmin::now(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            false, // sendInvite = false
        );

        $processManager = new UserInviteForMinimumProcessManager($commandBus);
        $processManager($event);
    }

    public function testDispatchesCorrectCommandData(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::on(fn ($command): bool => $command instanceof SendActivation
                && $command->userId()->toString() === $userId->toString()
                && $command->email()->toString() === $email->toString()
                && $command->firstName()->toString() === $firstName->toString()
                && $command->lastName()->toString() === $lastName->toString()))
            ->andReturn(new Envelope(new \stdClass()));

        $event = MinimalUserWasAddedByAdmin::now(
            $userId,
            $email,
            $faker->password(),
            Role::ROLE_ADMIN(),
            $firstName,
            $lastName,
            true,
        );

        $processManager = new UserInviteForMinimumProcessManager($commandBus);
        $processManager($event);
    }
}

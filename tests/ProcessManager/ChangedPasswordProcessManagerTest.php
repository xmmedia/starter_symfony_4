<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Command\SendPasswordChangedNotification;
use App\Model\User\Event\ChangedPassword;
use App\Model\User\Event\UserActivated;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\ProcessManager\ChangedPasswordProcessManager;
use App\Tests\BaseTestCase;
use Carbon\CarbonImmutable;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ChangedPasswordProcessManagerTest extends BaseTestCase
{
    public function testSendsNotificationWhenPasswordChanged(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Create events: UserActivated, then UserUpdatedProfile, then ChangedPassword
        $userActivatedEvent = UserActivated::now($userId);
        $this->setEventCreatedAt($userActivatedEvent, CarbonImmutable::now()->subDays(10));

        $userUpdatedEvent = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($userUpdatedEvent, CarbonImmutable::now()->subDays(5));

        $changedPasswordEvent = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        $events = [$userActivatedEvent, $userUpdatedEvent, $changedPasswordEvent];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)))
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendPasswordChangedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new ChangedPasswordProcessManager($userRepo, $commandBus);
        $processManager($changedPasswordEvent);
    }

    public function testDoesNotSendNotificationWhenPasswordChangedImmediatelyAfterActivation(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Create events: UserActivated, then ChangedPassword immediately after
        $userActivatedEvent = UserActivated::now($userId);
        $this->setEventCreatedAt($userActivatedEvent, CarbonImmutable::now()->subMinutes(5));

        $changedPasswordEvent = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        // UserActivated is the last event before ChangedPassword
        $events = [$userActivatedEvent, $changedPasswordEvent];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)))
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $processManager = new ChangedPasswordProcessManager($userRepo, $commandBus);
        $processManager($changedPasswordEvent);
    }

    public function testSendsNotificationForFirstPasswordChange(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Only one event: ChangedPassword (no previous UserActivated)
        $changedPasswordEvent = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        $events = [$changedPasswordEvent];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)))
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendPasswordChangedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new ChangedPasswordProcessManager($userRepo, $commandBus);
        $processManager($changedPasswordEvent);
    }

    public function testDoesNotSendNotificationWhenPasswordChangedWithin10Minutes(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Create two password changes within 10 minutes
        $firstPasswordChange = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($firstPasswordChange, CarbonImmutable::now()->subMinutes(5));

        $userUpdatedEvent = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($userUpdatedEvent, CarbonImmutable::now()->subMinutes(3));

        $secondPasswordChange = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($secondPasswordChange, CarbonImmutable::now());

        $events = [$firstPasswordChange, $userUpdatedEvent, $secondPasswordChange];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)))
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $processManager = new ChangedPasswordProcessManager($userRepo, $commandBus);
        $processManager($secondPasswordChange);
    }

    public function testSendsNotificationWhenPasswordChangedAfter10Minutes(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Create two password changes more than 10 minutes apart
        $firstPasswordChange = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($firstPasswordChange, CarbonImmutable::now()->subMinutes(15));

        $userUpdatedEvent = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($userUpdatedEvent, CarbonImmutable::now()->subMinutes(12));

        $secondPasswordChange = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($secondPasswordChange, CarbonImmutable::now());

        $events = [$firstPasswordChange, $userUpdatedEvent, $secondPasswordChange];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)))
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendPasswordChangedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new ChangedPasswordProcessManager($userRepo, $commandBus);
        $processManager($secondPasswordChange);
    }

    public function testSendsNotificationWithMixedEvents(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Various events with password change after UserUpdatedProfile events
        $userUpdatedEvent1 = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($userUpdatedEvent1, CarbonImmutable::now()->subDays(2));

        $userUpdatedEvent2 = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($userUpdatedEvent2, CarbonImmutable::now()->subDays(1));

        $changedPasswordEvent = ChangedPassword::now($userId, $faker->password());
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        $events = [$userUpdatedEvent1, $userUpdatedEvent2, $changedPasswordEvent];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)))
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendPasswordChangedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new ChangedPasswordProcessManager($userRepo, $commandBus);
        $processManager($changedPasswordEvent);
    }

    private function setEventCreatedAt($event, CarbonImmutable $date): void
    {
        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('createdAt');
        $property->setValue($event, $date->toDateTimeImmutable());
    }
}

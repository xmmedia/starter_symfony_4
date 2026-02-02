<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Command\SendPasswordChangedNotification;
use App\Model\User\Event\ChangedPassword;
use App\Model\User\Event\UserActivated;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\ProcessManager\ChangedPasswordProcessManager;
use App\Tests\BaseTestCase;
use Carbon\CarbonImmutable;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Tests\EventCreatedAtSetter;

class ChangedPasswordProcessManagerTest extends BaseTestCase
{
    use CreateUserUpdatedProfileEvent;
    use EventCreatedAtSetter;

    public function testSendsNotificationWhenPasswordChanged(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Create events: UserActivated, then UserUpdatedProfile, then ChangedPassword
        $userActivatedEvent = UserActivated::now($userId);
        $this->setEventCreatedAt($userActivatedEvent, CarbonImmutable::now()->subDays(10));

        $userUpdatedEvent = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($userUpdatedEvent, CarbonImmutable::now()->subDays(5));

        $changedPasswordEvent = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        $userRepo = $this->createUserRepo($userId, [$userActivatedEvent, $userUpdatedEvent, $changedPasswordEvent]);

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

        $changedPasswordEvent = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        // UserActivated is the last event before ChangedPassword
        $userRepo = $this->createUserRepo($userId, [$userActivatedEvent, $changedPasswordEvent]);

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
        $changedPasswordEvent = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        $userRepo = $this->createUserRepo($userId, [$changedPasswordEvent]);

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
        $firstPasswordChange = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($firstPasswordChange, CarbonImmutable::now()->subMinutes(5));

        $userUpdatedEvent = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($userUpdatedEvent, CarbonImmutable::now()->subMinutes(3));

        $secondPasswordChange = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($secondPasswordChange, CarbonImmutable::now());

        $userRepo = $this->createUserRepo($userId, [$firstPasswordChange, $userUpdatedEvent, $secondPasswordChange]);

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
        $firstPasswordChange = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($firstPasswordChange, CarbonImmutable::now()->subMinutes(15));

        $userUpdatedEvent = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($userUpdatedEvent, CarbonImmutable::now()->subMinutes(12));

        $secondPasswordChange = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($secondPasswordChange, CarbonImmutable::now());

        $userRepo = $this->createUserRepo($userId, [$firstPasswordChange, $userUpdatedEvent, $secondPasswordChange]);

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
        $userUpdatedEvent1 = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($userUpdatedEvent1, CarbonImmutable::now()->subDays(2));

        $userUpdatedEvent2 = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($userUpdatedEvent2, CarbonImmutable::now()->subDays(1));

        $changedPasswordEvent = $this->createChangedPasswordEvent($userId);
        $this->setEventCreatedAt($changedPasswordEvent, CarbonImmutable::now());

        $userRepo = $this->createUserRepo($userId, [$userUpdatedEvent1, $userUpdatedEvent2, $changedPasswordEvent]);

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendPasswordChangedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new ChangedPasswordProcessManager($userRepo, $commandBus);
        $processManager($changedPasswordEvent);
    }

    private function createChangedPasswordEvent(UserId $userId): ChangedPassword
    {
        return ChangedPassword::now($userId, $this->faker()->password());
    }

    private function createUserRepo(UserId $userId, array $events): UserList|\Mockery\MockInterface
    {
        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(static fn (UserId $arg): bool => $arg->sameValueAs($userId)))
            ->andReturn(new \ArrayIterator($events));

        return $userRepo;
    }
}

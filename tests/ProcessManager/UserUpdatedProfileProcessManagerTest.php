<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Command\SendProfileUpdatedNotification;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\ProcessManager\UserUpdatedProfileProcessManager;
use App\Tests\BaseTestCase;
use Carbon\CarbonImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Tests\EventCreatedAtSetter;

class UserUpdatedProfileProcessManagerTest extends BaseTestCase
{
    use CreateUserUpdatedProfileEvent;
    use EventCreatedAtSetter;

    public function testSendsNotificationForFirstProfileUpdate(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Only one event - the current update
        $updateEvent = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($updateEvent, CarbonImmutable::now());
        $this->setEventUuid($updateEvent, Uuid::uuid4());

        $userRepo = $this->createUserRepo($userId, [$updateEvent]);

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendProfileUpdatedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new UserUpdatedProfileProcessManager($userRepo, $commandBus);
        $processManager($updateEvent);
    }

    public function testDoesNotSendNotificationWhenUpdatedWithinOneHour(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $now = CarbonImmutable::now();

        // Two updates within 1 hour
        $firstUpdate = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($firstUpdate, $now->subMinutes(30));
        $this->setEventUuid($firstUpdate, Uuid::uuid4());

        $secondUpdate = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($secondUpdate, $now);
        $this->setEventUuid($secondUpdate, Uuid::uuid4());

        $userRepo = $this->createUserRepo($userId, [$firstUpdate, $secondUpdate]);

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $processManager = new UserUpdatedProfileProcessManager($userRepo, $commandBus);
        $processManager($secondUpdate);
    }

    public function testSendsNotificationWhenUpdatedAfterOneHour(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Two updates more than 1 hour apart
        $firstUpdate = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($firstUpdate, CarbonImmutable::now()->subHours(2));
        $this->setEventUuid($firstUpdate, Uuid::uuid4());

        $secondUpdate = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($secondUpdate, CarbonImmutable::now());
        $this->setEventUuid($secondUpdate, Uuid::uuid4());

        $userRepo = $this->createUserRepo($userId, [$firstUpdate, $secondUpdate]);

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendProfileUpdatedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new UserUpdatedProfileProcessManager($userRepo, $commandBus);
        $processManager($secondUpdate);
    }

    public function testIgnoresCurrentEventWhenFindingPreviousUpdate(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Three updates: first 3 hours ago, second 2 hours ago, third now
        $firstUpdate = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($firstUpdate, CarbonImmutable::now()->subHours(3));
        $this->setEventUuid($firstUpdate, Uuid::uuid4());

        $secondUpdate = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($secondUpdate, CarbonImmutable::now()->subHours(2));
        $this->setEventUuid($secondUpdate, Uuid::uuid4());

        $thirdUpdate = $this->createUserUpdatedProfileEvent($userId);
        $this->setEventCreatedAt($thirdUpdate, CarbonImmutable::now());
        $this->setEventUuid($thirdUpdate, Uuid::uuid4());

        $userRepo = $this->createUserRepo($userId, [$firstUpdate, $secondUpdate, $thirdUpdate]);

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendProfileUpdatedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new UserUpdatedProfileProcessManager($userRepo, $commandBus);
        $processManager($thirdUpdate);
    }

    private function createUserRepo(UserId $userId, array $events): UserList|\Mockery\MockInterface
    {
        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(static fn (UserId $arg): bool => $arg->sameValueAs($userId)), UserUpdatedProfile::class)
            ->andReturn(new \ArrayIterator($events));

        return $userRepo;
    }

    private function setEventUuid(AggregateChanged $event, \Ramsey\Uuid\UuidInterface $uuid): void
    {
        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('uuid');
        $property->setValue($event, $uuid);
    }
}

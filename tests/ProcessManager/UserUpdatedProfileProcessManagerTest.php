<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Command\SendProfileUpdatedNotification;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\ProcessManager\UserUpdatedProfileProcessManager;
use App\Tests\BaseTestCase;
use Carbon\CarbonImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserUpdatedProfileProcessManagerTest extends BaseTestCase
{
    public function testSendsNotificationForFirstProfileUpdate(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        // Only one event - the current update
        $updateEvent = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($updateEvent, CarbonImmutable::now());
        $this->setEventUuid($updateEvent, Uuid::uuid4());

        $events = [$updateEvent];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)), UserUpdatedProfile::class)
            ->andReturn(new \ArrayIterator($events));

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
        $firstUpdate = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($firstUpdate, $now->subMinutes(30));
        $this->setEventUuid($firstUpdate, Uuid::uuid4());

        $secondUpdate = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($secondUpdate, $now);
        $this->setEventUuid($secondUpdate, Uuid::uuid4());

        $events = [$firstUpdate, $secondUpdate];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)), UserUpdatedProfile::class)
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $processManager = new UserUpdatedProfileProcessManager($userRepo, $commandBus);
        $processManager($secondUpdate);
    }

    public function testSendsNotificationWhenUpdatedAfterOneHour(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $now = CarbonImmutable::now();

        // Two updates more than 1 hour apart
        $firstUpdate = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($firstUpdate, $now->subHours(2));
        $this->setEventUuid($firstUpdate, Uuid::uuid4());

        $secondUpdate = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($secondUpdate, $now);
        $this->setEventUuid($secondUpdate, Uuid::uuid4());

        $events = [$firstUpdate, $secondUpdate];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)), UserUpdatedProfile::class)
            ->andReturn(new \ArrayIterator($events));

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
        $now = CarbonImmutable::now();

        // Three updates: first 3 hours ago, second 2 hours ago, third now
        $firstUpdate = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($firstUpdate, $now->subHours(3));
        $this->setEventUuid($firstUpdate, Uuid::uuid4());

        $secondUpdate = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($secondUpdate, $now->subHours(2));
        $this->setEventUuid($secondUpdate, Uuid::uuid4());

        $thirdUpdate = UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
        $this->setEventCreatedAt($thirdUpdate, $now);
        $thirdUpdateUuid = Uuid::uuid4();
        $this->setEventUuid($thirdUpdate, $thirdUpdateUuid);

        $events = [$firstUpdate, $secondUpdate, $thirdUpdate];

        $userRepo = \Mockery::mock(UserList::class);
        $userRepo->shouldReceive('getEvents')
            ->once()
            ->with(\Mockery::on(fn (UserId $arg): bool => $arg->sameValueAs($userId)), UserUpdatedProfile::class)
            ->andReturn(new \ArrayIterator($events));

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendProfileUpdatedNotification::class))
            ->andReturn(new Envelope(new \stdClass()));

        $processManager = new UserUpdatedProfileProcessManager($userRepo, $commandBus);
        $processManager($thirdUpdate);
    }

    private function setEventCreatedAt($event, CarbonImmutable $date): void
    {
        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('createdAt');
        $property->setValue($event, \DateTimeImmutable::createFromMutable($date->toDateTime()));
    }

    private function setEventUuid($event, \Ramsey\Uuid\UuidInterface $uuid): void
    {
        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('uuid');
        $property->setValue($event, $uuid);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Model\User\Event;
use App\Model\User\Name;
use App\Projection\User\UserProjection;
use App\Projection\User\UserReadModel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Tests\ProjectionReadModel;
use Xm\SymfonyBundle\Tests\ProjectionWhenArgs;

class UserProjectionTest extends BaseTestCase
{
    use ProjectionReadModel;
    use ProjectionWhenArgs;

    private const array EXPECTED_TYPES = [
        'verified'  => 'boolean',
        'active'    => 'boolean',
        'roles'     => 'json',
        'user_data' => 'json',
    ];

    public function test(): void
    {
        $projectedEvents = [
            Event\UserWasAddedByAdmin::class,
            Event\MinimalUserWasAddedByAdmin::class,
            Event\UserWasUpdatedByAdmin::class,
            Event\AdminChangedPassword::class,
            Event\UserVerifiedByAdmin::class,
            Event\UserActivatedByAdmin::class,
            Event\UserDeactivatedByAdmin::class,
            Event\UserUpdatedProfile::class,
            Event\ChangedPassword::class,
            Event\PasswordUpgraded::class,
            Event\UserVerified::class,
            Event\UserActivated::class,
            Event\UserWasDeletedByAdmin::class,
        ];

        $projection = new UserProjection();

        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('user')
            ->andReturnSelf();

        $projector->shouldReceive('when')
            ->withArgs($this->whenArgs($projectedEvents));

        $projection->project($projector);
    }

    public function testUserWasAddedByAdmin(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $hashedPassword = $faker->password();
        $role = $faker->userRole();
        $active = $faker->boolean();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();
        $userData = $faker->userData();

        $event = Event\UserWasAddedByAdmin::now(
            $userId,
            $email,
            $hashedPassword,
            $role,
            $active,
            $firstName,
            $lastName,
            $sendInvite,
            $userData,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('insert', $stack[0][0]);
        $this->assertSame([
            [
                'user_id'    => $userId->toString(),
                'email'      => mb_strtolower($email->toString()),
                'password'   => $hashedPassword,
                'verified'   => !$sendInvite,
                'active'     => $active,
                'roles'      => [$role->getValue()],
                'first_name' => $firstName->toString(),
                'last_name'  => $lastName->toString(),
                'user_data'  => $userData->toArray(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testMinimalUserWasAddedByAdmin(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $hashedPassword = $faker->password();
        $role = $faker->userRole();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $sendInvite = $faker->boolean();

        $event = Event\MinimalUserWasAddedByAdmin::now(
            $userId,
            $email,
            $hashedPassword,
            $role,
            $firstName,
            $lastName,
            $sendInvite,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('insert', $stack[0][0]);
        $this->assertSame([
            [
                'user_id'    => $userId->toString(),
                'email'      => mb_strtolower($email->toString()),
                'password'   => $hashedPassword,
                'verified'   => !$sendInvite,
                'active'     => true,
                'roles'      => [$role->getValue()],
                'first_name' => $firstName->toString(),
                'last_name'  => $lastName->toString(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserWasUpdatedByAdmin(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $role = $faker->userRole();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $event = Event\UserWasUpdatedByAdmin::now(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName,
            $userData,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'email'      => mb_strtolower($email->toString()),
                'roles'      => [$role->getValue()],
                'first_name' => $firstName->toString(),
                'last_name'  => $lastName->toString(),
                'user_data'  => $userData->toArray(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testAdminChangedPassword(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $hashedPassword = $faker->password();

        $event = Event\AdminChangedPassword::now(
            $userId,
            $hashedPassword,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'password' => $hashedPassword,
            ],
        ], $stack[0][1]);
    }

    public function testUserVerifiedByAdmin(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserVerifiedByAdmin::now($userId);

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'verified' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserActivatedByAdmin(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserActivatedByAdmin::now($userId);

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'active' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserDeactivatedByAdmin(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserDeactivatedByAdmin::now($userId);

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'active' => false,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserUpdatedProfile(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());
        $userData = $faker->userData();

        $event = Event\UserUpdatedProfile::now(
            $userId,
            $email,
            $firstName,
            $lastName,
            $userData,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'email'      => mb_strtolower($email->toString()),
                'first_name' => $firstName->toString(),
                'last_name'  => $lastName->toString(),
                'user_data'  => $userData->toArray(),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testChangedPassword(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $hashedPassword = $faker->password();

        $event = Event\ChangedPassword::now(
            $userId,
            $hashedPassword,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'password' => $hashedPassword,
            ],
        ], $stack[0][1]);
    }

    public function testPasswordUpgraded(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $hashedPassword = $faker->password();

        $event = Event\PasswordUpgraded::now(
            $userId,
            $hashedPassword,
        );

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'password' => $hashedPassword,
            ],
        ], $stack[0][1]);
    }

    public function testUserVerified(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserVerified::now($userId);

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'verified' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserActivated(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserActivated::now($userId);

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('update', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
            [
                'verified' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserWasDeletedByAdmin(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserWasDeletedByAdmin::now($userId);

        $stack = $this->runReadModel($event);

        $this->assertCount(1, $stack);
        $this->assertSame('remove', $stack[0][0]);
        $this->assertSame([
            $userId->toString(),
        ], $stack[0][1]);
    }

    private function runReadModel(AggregateChanged $event): mixed
    {
        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        return $this->getReadModelStack($readModel);
    }
}

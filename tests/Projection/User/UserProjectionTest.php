<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Model\User\Event;
use App\Model\User\Name;
use App\Projection\User\UserProjection;
use App\Projection\User\UserReadModel;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Prooph\EventStore\Projection\AbstractReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\Tests\ProjectionReadModel;
use Xm\SymfonyBundle\Tests\ProjectionWhenArgs;
use Xm\SymfonyBundle\Util\Utils;

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

    public function testUserWasAddedByAdminCallsReadModelStack(): void
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

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('insert', $stack[0][0]);
        $this->assertEquals([
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

    public function testMinimalUserWasAddedByAdminCallsReadModelStack(): void
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

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('insert', $stack[0][0]);
        $this->assertEquals([
            [
                'user_id'    => $userId->toString(),
                'email'      => mb_strtolower($email->toString()),
                'password'   => $hashedPassword,
                'verified'   => !$sendInvite,
                'active'     => true,
                'roles'      => [$role->getValue()],
                'first_name' => Utils::serialize($firstName->toString()),
                'last_name'  => Utils::serialize($lastName->toString()),
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserWasUpdatedByAdminCallsReadModelStack(): void
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

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
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

    public function testAdminChangedPasswordCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $hashedPassword = $faker->password();

        $event = Event\AdminChangedPassword::now(
            $userId,
            $hashedPassword,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'password' => $hashedPassword,
            ],
        ], $stack[0][1]);
    }

    public function testUserVerifiedByAdminCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserVerifiedByAdmin::now(
            $userId,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'verified' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserActivatedByAdminCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserActivatedByAdmin::now(
            $userId,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'active' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserDeactivatedByAdminCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserDeactivatedByAdmin::now(
            $userId,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        // Use reflection to access the protected stack property from parent class
        $reflection = new \ReflectionClass(AbstractReadModel::class);

        $stackProperty = $reflection->getProperty('stack');

        $stack = $stackProperty->getValue($readModel);
        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'active' => false,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserUpdatedProfileCallsReadModelStack(): void
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

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        // Use reflection to access the protected stack property from parent class
        $reflection = new \ReflectionClass(AbstractReadModel::class);

        $stackProperty = $reflection->getProperty('stack');

        $stack = $stackProperty->getValue($readModel);
        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
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

    public function testChangedPasswordCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $hashedPassword = $faker->password();

        $event = Event\ChangedPassword::now(
            $userId,
            $hashedPassword,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        // Use reflection to access the protected stack property from parent class
        $reflection = new \ReflectionClass(AbstractReadModel::class);

        $stackProperty = $reflection->getProperty('stack');

        $stack = $stackProperty->getValue($readModel);
        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'password' => $hashedPassword,
            ],
        ], $stack[0][1]);
    }

    public function testPasswordUpgradedCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $hashedPassword = $faker->password();

        $event = Event\PasswordUpgraded::now(
            $userId,
            $hashedPassword,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        // Use reflection to access the protected stack property from parent class
        $reflection = new \ReflectionClass(AbstractReadModel::class);

        $stackProperty = $reflection->getProperty('stack');

        $stack = $stackProperty->getValue($readModel);
        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'password' => $hashedPassword,
            ],
        ], $stack[0][1]);
    }

    public function testUserVerifiedCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserVerified::now(
            $userId,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        $stack = $this->getReadModelStack($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'verified' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserActivatedCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserActivated::now(
            $userId,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        // Use reflection to access the protected stack property from parent class
        $reflection = new \ReflectionClass(AbstractReadModel::class);

        $stackProperty = $reflection->getProperty('stack');

        $stack = $stackProperty->getValue($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('update', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
            [
                'verified' => true,
            ],
            self::EXPECTED_TYPES,
        ], $stack[0][1]);
    }

    public function testUserWasDeletedByAdminCallsReadModelStack(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $event = Event\UserWasDeletedByAdmin::now(
            $userId,
        );

        $readModel = new UserReadModel(\Mockery::mock(Connection::class));

        $projector = $this->createReadModelMock('user', $event, $readModel);

        new UserProjection()->project($projector);

        // Use reflection to access the protected stack property from parent class
        $reflection = new \ReflectionClass(AbstractReadModel::class);

        $stackProperty = $reflection->getProperty('stack');

        $stack = $stackProperty->getValue($readModel);

        $this->assertCount(1, $stack);
        $this->assertEquals('remove', $stack[0][0]);
        $this->assertEquals([
            $userId->toString(),
        ], $stack[0][1]);
    }
}

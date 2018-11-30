<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\Email;
use App\Model\User\Command\SendActivation;
use App\Model\User\Event\UserWasCreatedByAdmin;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\ProcessManager\UserInviteProcessManager;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Role\Role;

class UserInviteProcessManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSend(): void
    {
        $faker = Faker\Factory::create();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(SendActivation::class))
            ->andReturn(new Envelope(new \StdClass()));

        $event = UserWasCreatedByAdmin::now(
            UserId::generate(),
            Email::fromString($faker->email),
            $faker->password,
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
        $faker = Faker\Factory::create();

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldNotReceive('dispatch');

        $event = UserWasCreatedByAdmin::now(
            UserId::generate(),
            Email::fromString($faker->email),
            $faker->password,
            new Role('ROLE_USER'),
            true,
            Name::fromString($faker->firstName),
            Name::fromString($faker->lastName),
            false
        );

        (new UserInviteProcessManager($commandBus))($event);
    }
}

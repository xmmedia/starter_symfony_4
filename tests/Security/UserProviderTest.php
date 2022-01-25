<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Model\User\Command\UpgradePassword;
use App\Security\UserProvider;
use App\Tests\BaseTestCase;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderTest extends BaseTestCase
{
    /** @var MessageBusInterface|Mockery\MockInterface */
    private $commandBus;

    protected function setUp(): void
    {
        $this->commandBus = Mockery::mock(MessageBusInterface::class);
    }

    public function testUpgradePassword(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());
        $user->shouldReceive('upgradePassword')->once();

        $this->commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(UpgradePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $this->getUserProvider()->upgradePassword($user, $faker->password());
    }

    public function testUpgradePasswordWrongUserType(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(UserInterface::class);

        $this->commandBus->shouldNotReceive('dispatch');

        $this->getUserProvider()->upgradePassword($user, $faker->password());
    }

    private function getUserProvider(): UserProvider
    {
        return new UserProvider(
            Mockery::mock(ManagerRegistry::class),
            $this->commandBus,
        );
    }
}

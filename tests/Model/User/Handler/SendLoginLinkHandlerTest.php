<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\SendLoginLink;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\SendLoginLinkHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class SendLoginLinkHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified');

        $user->shouldReceive('active');

        $user->shouldReceive('inviteSent');

        $command = SendLoginLink::now($faker->userId(), $faker->emailVo());

        $user = \Mockery::mock(\App\Entity\User::class);

        $user->shouldReceive('email')
            ->once()
            ->andReturn($command->email());
        $user->shouldReceive('name')
            ->once()
            ->andReturn($faker->name());

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('getReferencesEmail')
            ->once()
            ->andReturn($faker->email());
        $emailGateway->shouldReceive('send')
            ->andReturn(EmailGatewayMessageId::fromString($faker->uuid()));

        $loginLinkHandler = \Mockery::mock(LoginLinkHandlerInterface::class);
        $loginLinkHandler->shouldReceive('createLoginLink')
            ->once()
            ->andReturn(new LoginLinkDetails(
                $faker->string(10),
                \DateTimeImmutable::createFromMutable($faker->dateTime()),
            ));

        $handler = new SendLoginLinkHandler(
            $userFinder,
            $emailGateway,
            $faker->string(10),
            $faker->email(),
            $loginLinkHandler,
        );

        $handler($command);
    }

    public function testUserEntityNotFound(): void
    {
        $faker = $this->faker();

        $command = SendLoginLink::now($faker->userId(), $faker->emailVo());

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified');
        $user->shouldReceive('active');
        $user->shouldReceive('inviteSent');

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $loginLinkHandler = \Mockery::mock(LoginLinkHandlerInterface::class);

        $this->expectException(UserNotFound::class);

        $handler = new SendLoginLinkHandler(
            $userFinder,
            $emailGateway,
            $faker->string(10),
            $faker->email(),
            $loginLinkHandler,
        );

        $handler($command);
    }
}

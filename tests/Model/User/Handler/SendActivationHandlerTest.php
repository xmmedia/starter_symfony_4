<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\SendActivation;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\SendActivationHandler;
use App\Model\User\Name;
use App\Model\User\Token;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Security\TokenGeneratorInterface;
use App\Tests\BaseTestCase;
use Mockery;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class SendActivationHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('inviteSent')
            ->once();

        $command = SendActivation::now(
            $faker->userId,
            $faker->emailVo,
            Name::fromString($faker->name),
            Name::fromString($faker->name)
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        $emailGateway = Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('send')
            ->andReturn(EmailGatewayMessageId::fromString($faker->uuid()));
        $tokenGenerator = Mockery::mock(TokenGeneratorInterface::class);
        $tokenGenerator->shouldReceive('__invoke')
            ->andReturn(Token::fromString('string'));

        $router = Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->andReturn('url');

        $handler = new SendActivationHandler(
            $repo,
            $emailGateway,
            $faker->string(10),
            $router,
            $tokenGenerator
        );

        $handler($command);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = SendActivation::now(
            $faker->userId,
            $faker->emailVo,
            Name::fromString($faker->name),
            Name::fromString($faker->name)
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $emailGateway = Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('send')
            ->andReturn(EmailGatewayMessageId::fromString($faker->uuid()));
        $router = Mockery::mock(RouterInterface::class);
        $tokenGenerator = Mockery::mock(TokenGeneratorInterface::class);
        $tokenGenerator->shouldReceive('__invoke')
            ->andReturn(Token::fromString('string'));

        $this->expectException(UserNotFound::class);

        $handler = new SendActivationHandler(
            $repo,
            $emailGateway,
            $faker->string(10),
            $router,
            $tokenGenerator
        );

        $handler($command);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Infrastructure\Email\EmailGatewayInterface;
use App\Model\Email;
use App\Model\EmailGatewayMessageId;
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

        $emailGateway = new SendActivationHandlerTestEmailGateway();
        $tokenGenerator = new SendActivationHandlerTestTokenGenerator();

        $router = Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->andReturn('url');

        $handler = new SendActivationHandler(
            $repo,
            $emailGateway,
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

        $emailGateway = new SendActivationHandlerTestEmailGateway();
        $router = Mockery::mock(RouterInterface::class);
        $tokenGenerator = new SendActivationHandlerTestTokenGenerator();

        $this->expectException(UserNotFound::class);

        $handler = new SendActivationHandler(
            $repo,
            $emailGateway,
            $router,
            $tokenGenerator
        );

        $handler($command);
    }
}

class SendActivationHandlerTestEmailGateway implements EmailGatewayInterface
{
    public function send(
        int $templateId,
        Email $to,
        array $templateData
    ): EmailGatewayMessageId {
        return EmailGatewayMessageId::fromString(Uuid::uuid4()->toString());
    }
}

class SendActivationHandlerTestTokenGenerator implements TokenGeneratorInterface
{
    public function __invoke(): Token
    {
        return Token::fromString('string');
    }
}

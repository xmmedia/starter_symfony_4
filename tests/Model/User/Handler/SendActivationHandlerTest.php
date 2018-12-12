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
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class SendActivationHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('inviteSent')
            ->once();

        $command = SendActivation::now(
            UserId::generate(),
            Email::fromString($faker->email),
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
        $faker = Faker\Factory::create();

        $command = SendActivation::now(
            UserId::generate(),
            Email::fromString($faker->email),
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
        $faker = Faker\Factory::create();

        return EmailGatewayMessageId::fromString($faker->uuid);
    }
}

class SendActivationHandlerTestTokenGenerator implements TokenGeneratorInterface
{
    public function __invoke(): Token
    {
        return Token::fromString('string');
    }
}

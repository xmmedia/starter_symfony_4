<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Infrastructure\Email\EmailGatewayInterface;
use App\Model\Email;
use App\Model\EmailGatewayMessageId;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\InitiatePasswordRecoveryHandler;
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

class InitiatePasswordRecoveryHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('passwordRecoverySent')
            ->once();

        $command = InitiatePasswordRecovery::now(
            UserId::generate(),
            Email::fromString($faker->email)
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        $emailGateway = new InitiatePasswordRecoveryHandlerTestEmailGateway();
        $tokenGenerator = new InitiatePasswordRecoveryHandlerTestTokenGenerator();

        $router = Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->andReturn('url');

        (new InitiatePasswordRecoveryHandler(
            $repo, $emailGateway, $router, $tokenGenerator
        ))(
            $command
        );
    }

    public function testUserNotFound(): void
    {
        $faker = Faker\Factory::create();

        $command = InitiatePasswordRecovery::now(
            UserId::generate(),
            Email::fromString($faker->email)
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $emailGateway = new InitiatePasswordRecoveryHandlerTestEmailGateway();
        $router = Mockery::mock(RouterInterface::class);
        $tokenGenerator = new InitiatePasswordRecoveryHandlerTestTokenGenerator();

        $this->expectException(UserNotFound::class);

        (new InitiatePasswordRecoveryHandler(
            $repo, $emailGateway, $router, $tokenGenerator
        ))(
            $command
        );
    }
}

class InitiatePasswordRecoveryHandlerTestEmailGateway implements EmailGatewayInterface
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

class InitiatePasswordRecoveryHandlerTestTokenGenerator implements TokenGeneratorInterface
{
    public function __invoke(): Token
    {
        return Token::fromString('string');
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\InitiatePasswordRecoveryHandler;
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

class InitiatePasswordRecoveryHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('passwordRecoverySent')
            ->once();

        $command = InitiatePasswordRecovery::now(
            $faker->userId,
            $faker->emailVo
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
            $repo,
            $emailGateway,
            $faker->string(10),
            $router,
            $tokenGenerator
        ))(
            $command
        );
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = InitiatePasswordRecovery::now(
            $faker->userId,
            $faker->emailVo
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
            $repo,
            $emailGateway,
            $faker->string(10),
            $router,
            $tokenGenerator
        ))(
            $command
        );
    }
}

class InitiatePasswordRecoveryHandlerTestEmailGateway implements EmailGatewayInterface
{
    public function send(
        $templateIdOrAlias,
        $to,
        array $templateData,
        ?array $attachments = null,
        ?Email $from = null
    ): EmailGatewayMessageId {
        return EmailGatewayMessageId::fromString(Uuid::uuid4()->toString());
    }
}

class InitiatePasswordRecoveryHandlerTestTokenGenerator implements TokenGeneratorInterface
{
    public function __invoke(): Token
    {
        return Token::fromString('string');
    }
}

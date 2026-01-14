<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Infrastructure\Email\EmailTemplate;
use App\Model\User\Command\SendLoginLink;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\SendLoginLinkHandler;
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
        $email = $faker->emailVo();
        $url = $faker->url();
        $userName = $faker->name();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $command = SendLoginLink::now($faker->userId(), $faker->emailVo());

        $user = \Mockery::mock(\App\Entity\User::class);
        $user->shouldReceive('email')
            ->twice()
            ->andReturn($email);
        $user->shouldReceive('name')
            ->once()
            ->andReturn($userName);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $loginLinkHandler = \Mockery::mock(LoginLinkHandlerInterface::class);
        $loginLinkHandler->shouldReceive('createLoginLink')
            ->once()
            ->andReturn(
                new LoginLinkDetails(
                    $url,
                    \DateTimeImmutable::createFromMutable($faker->dateTime()),
                ),
            );

        $templateData = [
            'loginLinkUrl' => $url,
            'name'         => $userName,
            'email'        => $email->toString(),
        ];

        $headers = [
            'References' => $faker->email(),
        ];

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('getReferencesEmail')
            ->once()
            ->andReturn($headers['References']);
        $emailGateway->shouldReceive('send')
            ->with(
                EmailTemplate::AUTH_LOGIN_LINK,
                $email,
                $templateData,
                null,
                null,
                null,
                $headers,
            )
            ->andReturn($messageId);

        $handler = new SendLoginLinkHandler(
            $userFinder,
            $emailGateway,
            $faker->email(),
            $loginLinkHandler,
        );

        $handler($command);
    }

    public function testUserEntityNotFound(): void
    {
        $faker = $this->faker();

        $command = SendLoginLink::now($faker->userId(), $faker->emailVo());

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
            $faker->email(),
            $loginLinkHandler,
        );

        $handler($command);
    }
}

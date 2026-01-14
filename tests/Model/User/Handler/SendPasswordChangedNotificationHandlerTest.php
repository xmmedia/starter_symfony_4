<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Infrastructure\Email\EmailTemplate;
use App\Infrastructure\Service\UrlGenerator;
use App\Model\User\Command\SendPasswordChangedNotification;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\SendPasswordChangedNotificationHandler;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class SendPasswordChangedNotificationHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();
        $url = $faker->url();
        $userName = $faker->name();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $command = SendPasswordChangedNotification::now($faker->userId());

        $user = \Mockery::mock(\App\Entity\User::class);
        $user->shouldReceive('email')
            ->once()
            ->andReturn($email);
        $user->shouldReceive('name')
            ->once()
            ->andReturn($userName);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $urlGenerator = \Mockery::mock(UrlGenerator::class);
        $urlGenerator->shouldReceive('profile')
            ->once()
            ->andReturn($url);

        $templateData = [
            'name'       => $userName,
            'profileUrl' => $url,
        ];

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('send')
            ->with(
                EmailTemplate::USER_PASSWORD_CHANGED,
                $email,
                $templateData,
            )
            ->andReturn($messageId);

        $handler = new SendPasswordChangedNotificationHandler(
            $userFinder,
            $urlGenerator,
            $emailGateway,
        );

        $handler($command);
    }

    public function testUserEntityNotFound(): void
    {
        $faker = $this->faker();

        $command = SendPasswordChangedNotification::now($faker->userId());

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $urlGenerator = \Mockery::mock(UrlGenerator::class);
        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);

        $this->expectException(UserNotFound::class);

        $handler = new SendPasswordChangedNotificationHandler(
            $userFinder,
            $urlGenerator,
            $emailGateway,
        );

        $handler($command);
    }
}

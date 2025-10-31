<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Infrastructure\Service\UrlGenerator;
use App\Model\User\Command\SendProfileUpdatedNotification;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\SendProfileUpdatedNotificationHandler;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class SendProfileUpdatedNotificationHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $template = 'profile-updated-template';
        $email = $faker->emailVo();
        $url = $faker->url();
        $userName = $faker->name();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $command = SendProfileUpdatedNotification::now($userId);

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
            ->twice()
            ->andReturn($url);

        $templateData = [
            'name'       => $userName,
            'profileUrl' => $urlGenerator->profile(),
        ];

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('send')
            ->with(
                $template,
                $email,
                $templateData,
            )
            ->andReturn($messageId);

        $handler = new SendProfileUpdatedNotificationHandler(
            $userFinder,
            $urlGenerator,
            $emailGateway,
            $template,
        );

        $handler($command);
    }

    public function testUserEntityNotFound(): void
    {
        $faker = $this->faker();

        $command = SendProfileUpdatedNotification::now($faker->userId());

        $user = \Mockery::mock(User::class);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $urlGenerator = \Mockery::mock(UrlGenerator::class);
        $urlGenerator->shouldReceive('profile');

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);

        $this->expectException(UserNotFound::class);

        $handler = new SendProfileUpdatedNotificationHandler(
            $userFinder,
            $urlGenerator,
            $emailGateway,
            $faker->string(10),
        );

        $handler($command);
    }
}

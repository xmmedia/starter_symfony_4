<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\SendActivation;
use App\Model\User\Exception\UserAlreadyVerified;
use App\Model\User\Exception\UserNotActive;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\SendActivationHandler;
use App\Model\User\Name;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\UserList;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class SendActivationHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $template = 'activation-template';
        $email = $faker->emailVo();
        $url = $faker->url();
        $userName = $faker->name();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $userAr = \Mockery::mock(User::class);
        $userAr->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $userAr->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $command = SendActivation::now(
            $faker->userId(),
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
        );

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($userAr);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(User::class));

        $user = \Mockery::mock(\App\Entity\User::class);
        $user->shouldReceive('email')
            ->andReturn($email);
        $user->shouldReceive('name')
            ->once()
            ->andReturn($userName);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOrThrow')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $templateData = [
            'verifyUrl' => $url,
            'name'      => $userName,
            'email'     => $email->toString(),
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
                $template,
                $email,
                $templateData,
                null,
                null,
                null,
                $headers,
            )
            ->andReturn($messageId);

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->andReturn($url);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('generateResetToken')
            ->once()
            ->andReturn(new ResetPasswordToken('1234', new \DateTimeImmutable(), time()));

        $userAr->shouldReceive('inviteSent')
            ->once()
            ->with($messageId);

        $handler = new SendActivationHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $template,
            $faker->email(),
            $router,
            $resetPasswordHelper,
        );

        $handler($command);
    }

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();

        $command = SendActivation::now(
            $faker->userId(),
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
        );

        $userFinder = \Mockery::mock(UserFinder::class);

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $router = \Mockery::mock(RouterInterface::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $handler = new SendActivationHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $faker->string(10),
            $faker->email(),
            $router,
            $resetPasswordHelper,
        );

        $this->expectException(UserAlreadyVerified::class);

        $handler($command);
    }

    public function testInactive(): void
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();

        $command = SendActivation::now(
            $faker->userId(),
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
        );

        $userFinder = \Mockery::mock(UserFinder::class);

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $router = \Mockery::mock(RouterInterface::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $handler = new SendActivationHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $faker->string(10),
            $faker->email(),
            $router,
            $resetPasswordHelper,
        );

        $this->expectException(UserNotActive::class);

        $handler($command);
    }

    public function testUserArNotFound(): void
    {
        $faker = $this->faker();

        $command = SendActivation::now(
            $faker->userId(),
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
        );

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $userFinder = \Mockery::mock(UserFinder::class);
        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $router = \Mockery::mock(RouterInterface::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $this->expectException(UserNotFound::class);

        $handler = new SendActivationHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $faker->string(10),
            $faker->email(),
            $router,
            $resetPasswordHelper,
        );

        $handler($command);
    }

    public function testUserEntityNotFound(): void
    {
        $faker = $this->faker();

        $command = SendActivation::now(
            $faker->userId(),
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
        );

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOrThrow')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andThrow(UserNotFound::class);

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $router = \Mockery::mock(RouterInterface::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $this->expectException(UserNotFound::class);

        $handler = new SendActivationHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $faker->string(10),
            $faker->email(),
            $router,
            $resetPasswordHelper,
        );

        $handler($command);
    }
}

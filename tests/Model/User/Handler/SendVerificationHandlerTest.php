<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\SendVerification;
use App\Model\User\Exception\UserAlreadyVerified;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\SendVerificationHandler;
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

class SendVerificationHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();

        $user->shouldReceive('active');
        $user->shouldReceive('verificationSent');

        $command = SendVerification::now(
            $faker->userId(),
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
        );

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(User::class));

        $user = \Mockery::mock(\App\Entity\User::class);
        $user->shouldReceive('email');
        $user->shouldReceive('name');

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

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->andReturn('url');

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('generateResetToken')
            ->once()
            ->andReturn(new ResetPasswordToken('1234', new \DateTimeImmutable(), time()));

        $handler = new SendVerificationHandler(
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

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();

        $command = SendVerification::now(
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

        $handler = new SendVerificationHandler(
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

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = SendVerification::now(
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

        $handler = new SendVerificationHandler(
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

        $command = SendVerification::now(
            $faker->userId(),
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
        );

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('active');

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn($user);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $router = \Mockery::mock(RouterInterface::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $this->expectException(UserNotFound::class);

        $handler = new SendVerificationHandler(
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

<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Handler;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\Handler\InitiatePasswordRecoveryHandler;
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

class InitiatePasswordRecoveryHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userAr = \Mockery::mock(User::class);
        $userAr->shouldReceive('passwordRecoverySent')
            ->once();

        $command = InitiatePasswordRecovery::now(
            $faker->userId(),
            $faker->emailVo(),
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
            ->twice()
            ->andReturn($command->email());

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

        $passwordResetHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $passwordResetHelper->shouldReceive('generateResetToken')
            ->once()
            ->andReturn(new ResetPasswordToken('1234', new \DateTimeImmutable(), time()));

        new InitiatePasswordRecoveryHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $faker->email(),
            $router,
            $passwordResetHelper,
        )(
            $command
        );
    }

    public function testUserArNotFound(): void
    {
        $faker = $this->faker();

        $command = InitiatePasswordRecovery::now(
            $faker->userId(),
            $faker->emailVo(),
        );

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $userFinder = \Mockery::mock(UserFinder::class);
        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $router = \Mockery::mock(RouterInterface::class);
        $passwordResetHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $this->expectException(UserNotFound::class);

        new InitiatePasswordRecoveryHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $faker->email(),
            $router,
            $passwordResetHelper,
        )(
            $command
        );
    }

    public function testUserEntityNotFound(): void
    {
        $faker = $this->faker();

        $command = InitiatePasswordRecovery::now(
            $faker->userId(),
            $faker->emailVo(),
        );

        $repo = \Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturn(\Mockery::mock(User::class));

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(UserId::class))
            ->andReturnNull();

        $emailGateway = \Mockery::mock(EmailGatewayInterface::class);
        $router = \Mockery::mock(RouterInterface::class);
        $passwordResetHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $this->expectException(UserNotFound::class);

        new InitiatePasswordRecoveryHandler(
            $repo,
            $userFinder,
            $emailGateway,
            $faker->email(),
            $router,
            $passwordResetHelper,
        )(
            $command
        );
    }
}

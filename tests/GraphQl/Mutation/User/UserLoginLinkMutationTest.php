<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\GraphQl\Mutation\User\UserLoginLinkMutation;
use App\Model\User\Command\SendLoginLink;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;
use App\Tests\PwnedHttpClientMockTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

class UserLoginLinkMutationTest extends BaseTestCase
{
    use PwnedHttpClientMockTrait;
    use UserMockForUserMutationTrait;

    public function testUserLogInMutation(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(SendLoginLink::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId());
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($data['email']));

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->andReturn($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $result = (new UserLoginLinkMutation(
            $commandBus,
            $userFinder,
            $security,
            true,
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }

    public function testSecurity(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $userFinder = \Mockery::mock(UserFinder::class);
        $security = $this->createSecurity(true);

        $args = new Argument($data);

        $this->expectException(UserError::class);

        $result = (new UserLoginLinkMutation(
            $commandBus,
            $userFinder,
            $security,
            true,
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }

    public function testUserLogInUserNotFound(): void
    {
        $faker = $this->faker();
        $data = [
            'email' => $faker->email(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch');

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->andReturnFalse();
        $user->shouldReceive('userId');
        $user->shouldReceive('email');

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findOneByEmail')
            ->once()
            ->andReturnNull();

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $result = (new UserLoginLinkMutation(
            $commandBus,
            $userFinder,
            $security,
            true,
        ))(
            $args,
        );

        $this->assertEquals(['success' => true], $result);
    }
}

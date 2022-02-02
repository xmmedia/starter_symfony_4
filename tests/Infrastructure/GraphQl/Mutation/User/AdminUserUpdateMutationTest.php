<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Infrastructure\GraphQl\Mutation\User\AdminUserUpdateMutation;
use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Role;
use App\Security\PasswordHasher;
use App\Tests\BaseTestCase;
use App\Tests\PwnedHttpClientMockTrait;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;

class AdminUserUpdateMutationTest extends BaseTestCase
{
    use PwnedHttpClientMockTrait;

    public function test(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid(),
            'email'       => $faker->email(),
            'setPassword' => false,
            'firstName'   => $faker->name(),
            'lastName'    => $faker->name(),
            'role'        => 'ROLE_USER',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminUpdateUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = Mockery::mock(PasswordHasher::class);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserUpdateMutation(
            $commandBus,
            $passwordHasher,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testChangePassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid(),
            'email'       => $faker->email(),
            'setPassword' => true,
            'password'    => $faker->password(),
            'firstName'   => $faker->name(),
            'lastName'    => $faker->name(),
            'role'        => 'ROLE_USER',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminUpdateUser::class))
            ->andReturn(new Envelope(new \stdClass()));
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::type(Role::class), $data['password'])
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserUpdateMutation(
            $commandBus,
            $passwordHasher,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args
        );

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testPasswordTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid(),
            'email'       => $faker->email(),
            'setPassword' => true,
            'password'    => $faker->string(4097),
            'firstName'   => $faker->name(),
            'lastName'    => $faker->name(),
            'role'        => 'ROLE_USER',
        ];

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new AdminUserUpdateMutation(
            Mockery::mock(MessageBusInterface::class),
            Mockery::mock(PasswordHasher::class),
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))(
            $args
        );
    }
}

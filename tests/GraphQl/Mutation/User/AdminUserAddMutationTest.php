<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\GraphQl\Mutation\User\AdminUserAddMutation;
use App\Model\User\Command\AdminAddUser;
use App\Model\User\Role;
use App\Security\PasswordHasher;
use App\Tests\BaseTestCase;
use App\Tests\PwnedHttpClientMockTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;

class AdminUserAddMutationTest extends BaseTestCase
{
    use PwnedHttpClientMockTrait;

    public function testGeneratePassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->userId(),
            'email'       => $faker->email(),
            'setPassword' => false,
            'firstName'   => $faker->name(),
            'lastName'    => $faker->name(),
            'role'        => Role::byValue('ROLE_USER'),
            'active'      => true,
            'sendInvite'  => true,
            'userData'    => $faker->userData()->toArray(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserAddMutation(
            $commandBus,
            $passwordHasher,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $expected = [
            'userId' => $data['userId'],
            'email'  => $data['email'],
            'active' => $data['active'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testPasswordTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->userId(),
            'email'       => $faker->email(),
            'setPassword' => true,
            'password'    => $faker->string(4097),
            'firstName'   => $faker->name(),
            'lastName'    => $faker->name(),
            'role'        => Role::byValue('ROLE_USER'),
            'active'      => true,
            'sendInvite'  => true,
            'userData'    => $faker->userData()->toArray(),
        ];

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new AdminUserAddMutation(
            \Mockery::mock(MessageBusInterface::class),
            \Mockery::mock(PasswordHasher::class),
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testSetPassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->userId(),
            'email'       => $faker->email(),
            'setPassword' => true,
            'password'    => $faker->password(),
            'firstName'   => $faker->name(),
            'lastName'    => $faker->name(),
            'role'        => Role::byValue('ROLE_USER'),
            'active'      => true,
            'sendInvite'  => true,
            'userData'    => $faker->userData()->toArray(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->with(\Mockery::type(Role::class), $data['password'])
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserAddMutation(
            $commandBus,
            $passwordHasher,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $expected = [
            'userId' => $data['userId'],
            'email'  => $data['email'],
            'active' => $data['active'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testRoleAsString(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->userId(),
            'email'       => $faker->email(),
            'setPassword' => false,
            'firstName'   => $faker->name(),
            'lastName'    => $faker->name(),
            'role'        => Role::byValue('ROLE_USER')->getValue(),
            'active'      => true,
            'sendInvite'  => true,
            'userData'    => $faker->userData()->toArray(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserAddMutation(
            $commandBus,
            $passwordHasher,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $expected = [
            'userId' => $data['userId'],
            'email'  => $data['email'],
            'active' => $data['active'],
        ];

        $this->assertEquals($expected, $result);
    }
}

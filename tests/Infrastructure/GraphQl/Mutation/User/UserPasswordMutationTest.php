<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Mutation\User\UserPasswordMutation;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Role;
use App\Security\PasswordEncoder;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId;
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(ChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserPasswordMutation(
            $commandBus,
            $userPasswordEncoder,
            $passwordEncoder,
            $security
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testInvalidCurrentEmpty(?string $empty): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $empty,
            'newPassword'     => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordEncoder,
            $passwordEncoder,
            $security
        ))($args);
    }

    public function testInvalidCurrentPassword(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->andReturnFalse();

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordEncoder,
            $passwordEncoder,
            $security
        ))($args);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testInvalidNewEmpty(?string $empty): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => $empty,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordEncoder,
            $passwordEncoder,
            $security
        ))($args);
    }

    public function testInvalidNewTooShort(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => $faker->string(\App\Model\User\User::PASSWORD_MIN_LENGTH - 1),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordEncoder,
            $passwordEncoder,
            $security
        ))($args);
    }

    public function testInvalidNewTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => $faker->string(BasePasswordEncoder::MAX_PASSWORD_LENGTH + 1),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordEncoder,
            $passwordEncoder,
            $security
        ))($args);
    }

    public function testInvalidNewCompromised(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => '123456',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordEncoder,
            $passwordEncoder,
            $security
        ))($args);
    }

    public function emptyProvider(): \Generator
    {
        yield [''];
        yield [' '];
        yield ['   '];
        yield [null];
    }
}
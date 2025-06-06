<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\GraphQl\Mutation\User\UserPasswordMutation;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Security\PasswordHasher;
use App\Security\Security;
use App\Tests\BaseTestCase;
use App\Tests\PwnedHttpClientMockTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;

class UserPasswordMutationTest extends BaseTestCase
{
    use PwnedHttpClientMockTrait;
    use UserMockForUserMutationTrait;

    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $data = [
            'currentPassword' => $faker->password(),
            'newPassword'     => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(ChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $userPasswordHasher->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('email')
            ->once()
            ->andReturn($faker->emailVo());
        $user->shouldReceive('firstName')
            ->once()
            ->andReturn(Name::fromString($faker->firstName()));
        $user->shouldReceive('lastName')
            ->once()
            ->andReturn(Name::fromString($faker->lastName()));
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyProvider')]
    public function testInvalidCurrentEmpty(?string $empty): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $empty,
            'newPassword'     => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = \Mockery::mock(User::class);
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidCurrentPassword(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password(),
            'newPassword'     => $faker->password(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $userPasswordHasher->shouldReceive('isPasswordValid')
            ->andReturnFalse();

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = \Mockery::mock(User::class);
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyProvider')]
    public function testInvalidNewEmpty(?string $empty): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password(),
            'newPassword'     => $empty,
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $userPasswordHasher->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidNewTooShort(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password(),
            'newPassword'     => $faker->string(\App\Model\User\User::PASSWORD_MIN_LENGTH - 1),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $userPasswordHasher->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
        ))($args);
    }

    public function testInvalidNewTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password(),
            'newPassword'     => $faker->string(PasswordHasherInterface::MAX_PASSWORD_LENGTH + 1),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $userPasswordHasher->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidNotComplex(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password(),
            'newPassword'     => '123456',
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $userPasswordHasher->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidCompromised(): void
    {
        $faker = $this->faker();
        $password = $faker->password();
        $data = [
            'currentPassword' => $faker->password(),
            'newPassword'     => $password,
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $userPasswordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $userPasswordHasher->shouldReceive('isPasswordValid')
            ->andReturnTrue();

        $passwordHasher = \Mockery::mock(PasswordHasher::class);

        $user = $this->getUserMock();
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $pwnedHttpClient = new MockHttpClient([
            new MockResponse(substr(strtoupper(sha1($password)), 5).':5'),
        ]);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new UserPasswordMutation(
            $commandBus,
            $userPasswordHasher,
            $passwordHasher,
            $security,
            new PasswordStrengthFake(),
            $pwnedHttpClient,
        ))($args);
    }
}

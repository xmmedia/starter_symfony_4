<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\AddUserCommand;
use App\Entity\User;
use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Role;
use App\Projection\User\UserFinder;
use App\Security\PasswordHasher;
use App\Tests\BaseTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class AddUserCommandTest extends BaseTestCase
{
    public function testExecuteWithPasswordAndDefaultFormat(): void
    {
        $faker = $this->faker();
        $email = $faker->email();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUserMinimum::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('hashed-password');

        $userFinder = \Mockery::mock(UserFinder::class);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $router = \Mockery::mock(RouterInterface::class);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
        ]);

        $result = $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('Created new active user', $commandTester->getDisplay());
        $this->assertStringContainsString($email, $commandTester->getDisplay());
        $this->assertStringContainsString('ROLE_USER', $commandTester->getDisplay());
    }

    public function testExecuteWithSendInviteOption(): void
    {
        $faker = $this->faker();
        $email = $faker->email();
        $role = Role::ROLE_ADMIN();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUserMinimum::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('hashed-password');

        $userFinder = \Mockery::mock(UserFinder::class);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $router = \Mockery::mock(RouterInterface::class);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            $email,
            $role,
            $firstName,
            $lastName,
        ]);

        $result = $commandTester->execute(['--send-invite' => true]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Created new active user', $output);
        $this->assertStringContainsString('Invite sent to', $output);
        $this->assertStringContainsString($email, $output);
    }

    public function testExecuteWithGenerateActivationTokenOption(): void
    {
        $faker = $this->faker();
        $email = $faker->email();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $tokenValue = 'activation-token-123';
        $resetUrl = 'https://example.com/reset/activation-token-123';

        $user = \Mockery::mock(User::class);

        // Create a real ResetPasswordToken instance since it's final
        $resetToken = new ResetPasswordToken(
            $tokenValue,
            new \DateTimeImmutable('+1 hour'),
        );

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUserMinimum::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('hashed-password');

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->andReturn($user);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('generateResetToken')
            ->once()
            ->with($user)
            ->andReturn($resetToken);

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->once()
            ->with('user_reset_token', ['token' => $tokenValue], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn($resetUrl);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
        ]);

        $result = $commandTester->execute(['--generate-activation-token' => true]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Activation token', $output);
        $this->assertStringContainsString($tokenValue, $output);
        $this->assertStringContainsString('Reset URL', $output);
        $this->assertStringContainsString($resetUrl, $output);
    }

    public function testExecuteWithJsonFormat(): void
    {
        $faker = $this->faker();
        $email = $faker->email();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUserMinimum::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('hashed-password');

        $userFinder = \Mockery::mock(UserFinder::class);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);

        $router = \Mockery::mock(RouterInterface::class);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
        ]);

        $result = $commandTester->execute(['--format' => 'json']);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $commandTester->getDisplay();

        // Extract the JSON from output (may have some extra output before/after)
        $lines = explode("\n", trim($output));
        $jsonLine = end($lines);

        $json = json_decode($jsonLine, true);
        $this->assertIsArray($json, 'Failed to decode JSON from: '.$jsonLine);
        $this->assertArrayHasKey('userId', $json);
        $this->assertArrayHasKey('email', $json);
        $this->assertArrayHasKey('role', $json);
        $this->assertEquals($email, $json['email']);
        $this->assertEquals($role, $json['role']);
    }

    public function testExecuteWithJsonFormatAndActivationToken(): void
    {
        $faker = $this->faker();
        $email = $faker->email();
        $password = $faker->password();
        $role = Role::ROLE_SUPER_ADMIN();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $tokenValue = 'activation-token-456';
        $resetUrl = 'https://example.com/reset/activation-token-456';

        $user = \Mockery::mock(User::class);

        // Create a real ResetPasswordToken instance since it's final
        $resetToken = new ResetPasswordToken(
            $tokenValue,
            new \DateTimeImmutable('+1 hour'),
        );

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUserMinimum::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('hashed-password');

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->andReturn($user);

        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->shouldReceive('generateResetToken')
            ->once()
            ->with($user)
            ->andReturn($resetToken);

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->once()
            ->with('user_reset_token', ['token' => $tokenValue], UrlGeneratorInterface::ABSOLUTE_URL)
            ->andReturn($resetUrl);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            $email,
            $password,
            $role,
            $firstName,
            $lastName,
        ]);

        $result = $commandTester->execute([
            '--generate-activation-token' => true,
            '--format'                    => 'json',
        ]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $commandTester->getDisplay();

        // Extract the JSON from output (may have some extra output before/after)
        $lines = explode("\n", trim($output));
        $jsonLine = end($lines);

        $json = json_decode($jsonLine, true);
        $this->assertIsArray($json, 'Failed to decode JSON from: '.$jsonLine);
        $this->assertArrayHasKey('userId', $json);
        $this->assertArrayHasKey('email', $json);
        $this->assertArrayHasKey('role', $json);
        $this->assertArrayHasKey('activationToken', $json);
        $this->assertArrayHasKey('resetUrl', $json);
        $this->assertEquals($email, $json['email']);
        $this->assertEquals($role, $json['role']);
        $this->assertEquals($tokenValue, $json['activationToken']);
        $this->assertEquals($resetUrl, $json['resetUrl']);
    }

    public function testExecuteThrowsExceptionWhenSendInviteAndGenerateActivationTokenBothProvided(): void
    {
        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $userFinder = \Mockery::mock(UserFinder::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $router = \Mockery::mock(RouterInterface::class);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('send-invite and generate-activation-token cannot be used together');

        $commandTester->execute([
            '--send-invite'               => true,
            '--generate-activation-token' => true,
        ]);
    }

    public function testExecuteValidatesEmail(): void
    {
        $faker = $this->faker();
        $invalidEmail = 'not-an-email';
        $validEmail = $faker->email();
        $password = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUserMinimum::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('hashed-password');

        $userFinder = \Mockery::mock(UserFinder::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $router = \Mockery::mock(RouterInterface::class);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);
        // First input: invalid email, second: valid email
        $commandTester->setInputs([
            $invalidEmail,
            $validEmail,
            $password,
            $role,
            $firstName,
            $lastName,
        ]);

        $result = $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('email address in invalid', $output);
    }

    public function testExecuteValidatesPasswordLength(): void
    {
        $faker = $this->faker();
        $email = $faker->email();
        $shortPassword = 'short';
        $validPassword = $faker->password();
        $role = Role::ROLE_USER();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAddUserMinimum::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordHasher = \Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('__invoke')
            ->once()
            ->andReturn('hashed-password');

        $userFinder = \Mockery::mock(UserFinder::class);
        $resetPasswordHelper = \Mockery::mock(ResetPasswordHelperInterface::class);
        $router = \Mockery::mock(RouterInterface::class);

        $command = new AddUserCommand(
            $commandBus,
            $passwordHasher,
            $userFinder,
            $resetPasswordHelper,
            $router,
        );

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            $email,
            $shortPassword,
            $validPassword,
            $role,
            $firstName,
            $lastName,
        ]);

        $result = $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('at least', $output);
    }
}

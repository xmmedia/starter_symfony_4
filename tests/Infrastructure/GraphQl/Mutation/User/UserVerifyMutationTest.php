<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Mutation\User\UserVerifyMutation;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Command\VerifyUser;
use App\Model\User\Exception\InvalidToken;
use App\Model\User\Exception\TokenHasExpired;
use App\Model\User\Role;
use App\Model\User\Token;
use App\Security\PasswordEncoder;
use App\Security\Security;
use App\Security\TokenValidator;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

class UserVerifyMutationTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(VerifyUser::class))
            ->andReturn(new Envelope(new \stdClass()));
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(ChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andReturn($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $result = (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testLoggedIn(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $tokenValidator = Mockery::mock(TokenValidator::class);

        $security = $this->createSecurity(true);

        $args = new Argument($data);

        $this->expectException(UserError::class);

        $result = (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andReturn($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);

        $result = (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenExpired(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andThrow(TokenHasExpired::before(new Token('string'), '24 hours'));

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(405);

        $result = (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andThrow(InvalidToken::tokenDoesntExist(new Token('string')));

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        $result = (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testInvalidNewEmpty(?string $empty): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $empty,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);
    }

    public function testInvalidNewTooShort(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $faker->string(\App\Model\User\User::PASSWORD_MIN_LENGTH - 1),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);
    }

    public function testInvalidNewTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => $faker->string(BasePasswordEncoder::MAX_PASSWORD_LENGTH + 1),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security
        ))($args);
    }

    public function testInvalidNewCompromised(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password,
            'password' => '123456',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
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

    private function createSecurity(bool $isGrantedResult): Security
    {
        $symfonySecurity = Mockery::mock(
            \Symfony\Component\Security\Core\Security::class
        );
        $symfonySecurity->shouldReceive('isGranted')
            ->once()
            ->andReturn($isGrantedResult);

        return new Security($symfonySecurity);
    }
}

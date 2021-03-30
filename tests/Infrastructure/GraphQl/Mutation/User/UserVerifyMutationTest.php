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
use App\Security\TokenValidator;
use App\Tests\BaseTestCase;
use App\Tests\PwnedHttpClientMockTrait;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;

class UserVerifyMutationTest extends BaseTestCase
{
    use PwnedHttpClientMockTrait;
    use UserMockForUserMutationTrait;

    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => $faker->password(),
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

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());

        $tokenValidator = $this->getTokenValidator($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $result = (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testLoggedIn(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => $faker->password(),
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
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => $faker->password(),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = $this->getUserMock();
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();

        $tokenValidator = $this->getTokenValidator($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(UserError::class);

        $result = (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenExpired(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => $faker->password(),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

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
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => $faker->password(),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

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
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
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
            'token'    => $faker->password(),
            'password' => $empty,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $tokenValidator = $this->getTokenValidator($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidNewTooShort(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => $faker->string(\App\Model\User\User::PASSWORD_MIN_LENGTH - 1),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $tokenValidator = $this->getTokenValidator($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient(),
        ))($args);
    }

    public function testInvalidNewTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => $faker->string(BasePasswordEncoder::MAX_PASSWORD_LENGTH + 1),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $tokenValidator = $this->getTokenValidator($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security,
            new PasswordStrengthFake(),
        ))($args);
    }

    public function testInvalidCompromised(): void
    {
        $faker = $this->faker();
        $password = $faker->password();
        $data = [
            'token'    => $faker->password(),
            'password' => $password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $tokenValidator = $this->getTokenValidator($user);

        $security = $this->createSecurity(false);

        $pwnedHttpClient = new MockHttpClient([
            new MockResponse(substr(strtoupper(sha1($password)), 5).':5'),
        ]);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security,
            new PasswordStrengthFake(),
            $pwnedHttpClient
        ))($args);
    }

    public function testInvalidNotComplex(): void
    {
        $faker = $this->faker();
        $data = [
            'token'    => $faker->password(),
            'password' => '123456',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = $this->getUserMock();
        $user->shouldReceive('userId')
            ->andReturn($faker->userId());

        $tokenValidator = $this->getTokenValidator($user);

        $security = $this->createSecurity(false);

        $args = new Argument($data);

        $this->expectException(\InvalidArgumentException::class);

        (new UserVerifyMutation(
            $commandBus,
            $passwordEncoder,
            $tokenValidator,
            $security,
            new PasswordStrengthFake(),
            $this->getPwnedHttpClient()
        ))($args);
    }
}

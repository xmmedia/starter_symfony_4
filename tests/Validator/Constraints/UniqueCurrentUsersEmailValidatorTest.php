<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\User;
use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserId;
use App\Validator\Constraints\UniqueCurrentUsersEmail;
use App\Validator\Constraints\UniqueCurrentUsersEmailValidator;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class UniqueCurrentUsersEmailValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testUnique(): void
    {
        $faker = Faker\Factory::create();

        $uniqueChecker = new UniqueCurrentUsersEmailValidatorTestUniquenessCheckerNone();

        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('id')
            ->andReturn(UserId::generate());

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueCurrentUsersEmailValidator(
            $uniqueChecker,
            $this->createSecurity($user)
        );

        $validator->validate(Email::fromString($faker->email), $constraint);
    }

    public function testSameUser(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $uniqueChecker = new UniqueCurrentUsersEmailValidatorTestUniquenessCheckerDuplicate($userId);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('id')
            ->andReturn($userId);

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueCurrentUsersEmailValidator(
            $uniqueChecker,
            $this->createSecurity($user)
        );

        $validator->validate(Email::fromString($faker->email), $constraint);
    }

    public function testNotUnique(): void
    {
        $faker = Faker\Factory::create();

        $uniqueChecker = new UniqueCurrentUsersEmailValidatorTestUniquenessCheckerDuplicate(UserId::generate());

        $user = Mockery::mock(User::class);
        $user->shouldReceive('id')
            ->andReturn(UserId::generate());

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueCurrentUsersEmailValidator(
            $uniqueChecker,
            $this->createSecurity($user)
        );

        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $builder->shouldReceive('addViolation')
            ->once();

        $context = \Mockery::mock(ExecutionContext::class);
        $context->shouldReceive('buildViolation')
            ->once()
            ->with($constraint->message)
            ->andReturn($builder);

        $validator->initialize($context);

        $validator->validate(Email::fromString($faker->email), $constraint);
    }

    /**
     * $user: false = no token storage within container, null = no user.
     *
     * @param UserInterface|bool|null $user
     */
    private function createSecurity($user): Security
    {
        $tokenStorage = Mockery::mock(TokenStorageInterface::class);

        if (false !== $user) {
            $token = Mockery::mock(TokenInterface::class);
            $token->shouldReceive('getUser')
                ->andReturn($user)
            ;

            $tokenStorage->shouldReceive('getToken')
                ->andReturn($token)
            ;
        }

        $container = $this->createContainer('security.token_storage', $tokenStorage);

        return new Security($container);
    }

    private function createContainer($serviceId, $serviceObject): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with($serviceId)
            ->andReturn($serviceObject);

        return $container;
    }
}

class UniqueCurrentUsersEmailValidatorTestUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return null;
    }
}

class UniqueCurrentUsersEmailValidatorTestUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
{
    /** @var UserId */
    private $userId;

    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
    }


    public function __invoke(Email $email): ?UserId
    {
        return $this->userId;
    }
}

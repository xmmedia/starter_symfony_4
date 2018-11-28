<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\User;
use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserId;
use App\Validator\Constraints\UniqueCurrentUsersEmail;
use App\Validator\Constraints\UniqueNewUserEmailValidator;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class UniqueNewUserEmailValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testUnique(): void
    {
        $faker = Faker\Factory::create();

        $uniqueChecker = new UniqueNewUserEmailValidatorTestUniquenessCheckerNone();

        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('id')
            ->andReturn(UserId::generate());

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueNewUserEmailValidator($uniqueChecker);

        $validator->validate(
            Email::fromString($faker->email),
            $constraint
        );
    }

    public function testNotUnique(): void
    {
        $faker = Faker\Factory::create();

        $uniqueChecker = new UniqueNewUserEmailValidatorTestUniquenessCheckerDuplicate(
            UserId::generate()
        );

        $user = Mockery::mock(User::class);
        $user->shouldReceive('id')
            ->andReturn(UserId::generate());

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueNewUserEmailValidator($uniqueChecker);

        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $builder->shouldReceive('addViolation')
            ->once();

        $context = \Mockery::mock(ExecutionContext::class);
        $context->shouldReceive('buildViolation')
            ->once()
            ->with($constraint->message)
            ->andReturn($builder);

        $validator->initialize($context);

        $validator->validate(
            Email::fromString($faker->email),
            $constraint
        );
    }
}

class UniqueNewUserEmailValidatorTestUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId
    {
        return null;
    }
}

class UniqueNewUserEmailValidatorTestUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
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

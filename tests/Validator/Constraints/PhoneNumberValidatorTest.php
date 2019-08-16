<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Tests\BaseTestCase;
use App\Tests\Model\PhoneNumberDataProvider;
use App\Validator\Constraints\PhoneNumber;
use App\Validator\Constraints\PhoneNumberValidator;
use Mockery;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class PhoneNumberValidatorTest extends BaseTestCase
{
    use PhoneNumberDataProvider;

    /**
     * @dataProvider phoneNumberValidProvider
     */
    public function testValid(string $string): void
    {
        $constraint = Mockery::mock(PhoneNumber::class);

        $validator = new PhoneNumberValidator();

        $context = Mockery::mock(ExecutionContext::class);
        $context->shouldNotReceive('buildViolation');

        $validator->initialize($context);

        $validator->validate($string, $constraint);
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testEmpty(?string $string): void
    {
        $constraint = Mockery::mock(PhoneNumber::class);

        $validator = new PhoneNumberValidator();

        $context = Mockery::mock(ExecutionContext::class);
        $context->shouldNotReceive('buildViolation');

        $validator->initialize($context);

        $validator->validate($string, $constraint);
    }

    public function emptyProvider(): \Generator
    {
        yield [''];
        yield [null];
    }

    /**
     * @dataProvider phoneNumberInvalidProvider
     */
    public function testInvalid(string $string): void
    {
        $constraint = Mockery::mock(PhoneNumber::class);

        $validator = new PhoneNumberValidator();

        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $builder->shouldReceive('addViolation')
            ->once();

        $context = Mockery::mock(ExecutionContext::class);
        $context->shouldReceive('buildViolation')
            ->once()
            ->with($constraint->message)
            ->andReturn($builder);

        $validator->initialize($context);

        $validator->validate($string, $constraint);
    }
}

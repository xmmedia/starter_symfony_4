<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Tests\BaseTestCase;
use App\Tests\EmptyProvider;
use App\Util\Assert;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class AssertTest extends BaseTestCase
{
    use EmptyProvider;

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testPasswordLengthValid(): void
    {
        Assert::passwordLength($this->faker()->string(12));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyProvider')]
    public function testPasswordLengthEmpty(?string $password): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The password cannot be empty or all whitespace');

        Assert::passwordLength($password);
    }

    public function testPasswordLengthTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The password must length must be between');

        Assert::passwordLength($this->faker()->string(11));
    }

    public function testPasswordLengthTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The password must length must be between');

        Assert::passwordLength(
            $this->faker()->string(PasswordHasherInterface::MAX_PASSWORD_LENGTH + 1),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Util\Assert;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

class AssertTest extends BaseTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testPasswordLengthValid(): void
    {
        Assert::passwordLength($this->faker()->string(12));
    }

    /**
     * @dataProvider emptyProvider
     */
    public function testPasswordLengthEmpty(?string $password): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The password cannot be empty or all whitespace');

        Assert::passwordLength($password);
    }

    public function emptyProvider(): \Generator
    {
        yield [''];
        yield [' '];
        yield ['   '];
        yield [null];
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
            $this->faker()->string(BasePasswordEncoder::MAX_PASSWORD_LENGTH + 1)
        );
    }
}

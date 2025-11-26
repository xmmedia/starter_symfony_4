<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Money;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\ValueObject;

class MoneyTest extends BaseTestCase
{
    public function testFromIntCreatesMoneyInstance(): void
    {
        $faker = $this->faker();
        $cents = $faker->numberBetween(Money::MIN, Money::MAX);

        $money = Money::fromInt($cents);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals((string) $cents, $money->toString());
    }

    public function testFromStringCreatesMoneyInstance(): void
    {
        $cents = '10000'; // $100.00
        $money = Money::fromString($cents);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals($cents, $money->toString());
    }

    public function testFromMoneyCreatesMoneyInstance(): void
    {
        $faker = $this->faker();
        $number = $faker->numberBetween(Money::MIN, Money::MAX);

        $money = Money::fromMoney(\Money\Money::CAD($number));

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals($number, $money->toString());
    }

    public function testMinimumValueAllowed(): void
    {
        $money = Money::fromInt(Money::MIN);

        $this->assertEquals((string) Money::MIN, $money->toString());
    }

    public function testMaximumValueAllowed(): void
    {
        $money = Money::fromInt(Money::MAX);

        $this->assertEquals((string) Money::MAX, $money->toString());
    }

    public function testThrowsExceptionWhenBelowMinimum(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Money amount must be between');

        Money::fromInt(-1);
    }

    public function testThrowsExceptionWhenAboveMaximum(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Money amount must be between');

        Money::fromInt(Money::MAX + 1);
    }

    public function testFormattedReturnsCurrencyString(): void
    {
        $faker = $this->faker();
        $money = Money::fromInt($faker->numberBetween(Money::MIN, Money::MAX));

        $formatted = $money->formatted();

        $this->assertStringStartsWith('$', $formatted);
    }

    public function testMoneyReturnsInternalMoneyObject(): void
    {
        $faker = $this->faker();
        $money = Money::fromInt($faker->numberBetween(Money::MIN, Money::MAX));

        $internalMoney = $money->money();

        $this->assertInstanceOf(\Money\Money::class, $internalMoney);
        $this->assertEquals($money, $internalMoney->getAmount());
    }

    public function testSameValueAsReturnsTrueForEqualMoney(): void
    {
        $faker = $this->faker();
        $money = $faker->numberBetween(Money::MIN, Money::MAX);

        $money1 = Money::fromInt($money);
        $money2 = Money::fromInt($money);

        $this->assertTrue($money1->sameValueAs($money2));
    }

    public function testSameValueAsReturnsFalseForDifferentMoney(): void
    {
        $faker = $this->faker();

        $money1 = Money::fromInt($faker->numberBetween(Money::MIN, Money::MAX));
        $money2 = Money::fromInt($faker->numberBetween(Money::MIN, Money::MAX));

        $this->assertFalse($money1->sameValueAs($money2));
    }

    public function testSameValueAsReturnsFalseForDifferentClass(): void
    {
        $money = Money::fromInt(5000);
        $other = \Mockery::mock(ValueObject::class);

        $this->assertFalse($money->sameValueAs($other));
    }
}

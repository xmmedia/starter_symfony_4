<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Money;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class MoneyTest extends BaseTestCase
{
    public function testFromInt(): void
    {
        $faker = $this->faker();
        $cents = $faker->numberBetween(Money::MIN, Money::MAX);

        $money = Money::fromInt($cents);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame((string) $cents, $money->toString());
    }

    public function testFromString(): void
    {
        $cents = '10000'; // $100.00
        $money = Money::fromString($cents);

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame($cents, $money->toString());
    }

    public function testFromMoney(): void
    {
        $faker = $this->faker();
        $number = $faker->numberBetween(Money::MIN, Money::MAX);

        $money = Money::fromMoney(\Money\Money::CAD($number));

        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame((string) $number, $money->toString());
    }

    public function testMinimum(): void
    {
        $money = Money::fromInt(Money::MIN);

        $this->assertSame((string) Money::MIN, $money->toString());
    }

    public function testMaximum(): void
    {
        $money = Money::fromInt(Money::MAX);

        $this->assertSame((string) Money::MAX, $money->toString());
    }

    public function testTooLow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Money amount must be between');

        Money::fromInt(-1);
    }

    public function testTooHigh(): void
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

    public function testSameValueAs(): void
    {
        $faker = $this->faker();
        $money = $faker->numberBetween(Money::MIN, Money::MAX);

        $money1 = Money::fromInt($money);
        $money2 = Money::fromInt($money);

        $this->assertTrue($money1->sameValueAs($money2));
    }

    public function testSameValueAsFalse(): void
    {
        $faker = $this->faker();

        $money1 = Money::fromInt($faker->unique()->numberBetween(Money::MIN, Money::MAX));
        $money2 = Money::fromInt($faker->unique()->numberBetween(Money::MIN, Money::MAX));

        $this->assertFalse($money1->sameValueAs($money2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $money = Money::fromInt(5000);

        $this->assertFalse($money->sameValueAs(FakeVo::create()));
    }
}

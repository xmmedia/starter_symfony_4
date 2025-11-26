<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Money;
use App\Tests\BaseTestCase;

class MoneyTest extends BaseTestCase
{
    public function testFromIntCreatesMoneyInstance(): void
    {
        $cents = 5000; // $50.00
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
        $money = Money::fromMoney(\Money\Money::CAD(7500));

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals('7500', $money->toString());
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
        $money = Money::fromInt(12345); // $123.45

        $formatted = $money->formatted();

        // Should contain currency symbol and amount
        $this->assertStringContainsString('123', $formatted);
        $this->assertStringContainsString('45', $formatted);
    }

    public function testToStringReturnsAmountInCents(): void
    {
        $cents = '5000';
        $money = Money::fromString($cents);

        $this->assertEquals($cents, (string) $money);
    }

    public function testMoneyReturnsInternalMoneyObject(): void
    {
        $money = Money::fromInt(2500);

        $internalMoney = $money->money();

        $this->assertInstanceOf(\Money\Money::class, $internalMoney);
        $this->assertEquals('2500', $internalMoney->getAmount());
    }

    public function testSameValueAsReturnsTrueForEqualMoney(): void
    {
        $money1 = Money::fromInt(5000);
        $money2 = Money::fromInt(5000);

        $this->assertTrue($money1->sameValueAs($money2));
    }

    public function testSameValueAsReturnsFalseForDifferentMoney(): void
    {
        $money1 = Money::fromInt(5000);
        $money2 = Money::fromInt(6000);

        $this->assertFalse($money1->sameValueAs($money2));
    }

    public function testSameValueAsReturnsFalseForDifferentClass(): void
    {
        $money = Money::fromInt(5000);
        $other = \Mockery::mock(\Xm\SymfonyBundle\Model\ValueObject::class);

        $this->assertFalse($money->sameValueAs($other));
    }

    public function testZeroValueAllowed(): void
    {
        $money = Money::fromInt(0);

        $this->assertEquals('0', $money->toString());
    }

    public function testLargeValidValue(): void
    {
        $cents = 9999999; // $99,999.99
        $money = Money::fromInt($cents);

        $this->assertEquals((string) $cents, $money->toString());
    }

    public function testFromStringThrowsExceptionWithLeadingZeros(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Leading zeros are not allowed');

        Money::fromString('00500');
    }

    public function testGetFormatterReturnsIntlMoneyFormatter(): void
    {
        $formatter = Money::getFormatter();

        $this->assertInstanceOf(\Money\Formatter\IntlMoneyFormatter::class, $formatter);
    }
}

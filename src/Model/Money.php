<?php

declare(strict_types=1);

namespace App\Model;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Xm\SymfonyBundle\Model\MoneyFactory;
use Xm\SymfonyBundle\Model\ValueObject;

class Money implements ValueObject
{
    public const MIN = 0;
    public const MAX = 100_000_00; // $100,000.00

    protected \Money\Money $money;

    /**
     * @return static
     */
    public static function fromInt(int $cents): self
    {
        // @phpstan-ignore-next-line
        return new static((string) $cents);
    }

    /**
     * @return static
     */
    public static function fromString(string $cents): self
    {
        // @phpstan-ignore-next-line
        return new static($cents);
    }

    /**
     * @return static
     */
    public static function fromMoney(\Money\Money $money): self
    {
        // @phpstan-ignore-next-line
        return new static($money->getAmount());
    }

    private function __construct(string $cents)
    {
        $money = MoneyFactory::fromString($cents);

        $min = MoneyFactory::fromInt(static::MIN);
        $max = MoneyFactory::fromInt(static::MAX);

        if (!$money->greaterThanOrEqual($min) || !$money->lessThanOrEqual($max)) {
            throw new \InvalidArgumentException(sprintf('The Money amount must be between %s and %s cents. Got %s cents.', $min->getAmount(), $max->getAmount(), $money->getAmount()));
        }

        $this->money = $money;
    }

    public function money(): \Money\Money
    {
        return $this->money;
    }

    public function formatted(): string
    {
        return self::getFormatter()->format($this->money);
    }

    public function toString(): string
    {
        return $this->money->getAmount();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public static function getFormatter(): IntlMoneyFormatter
    {
        $numberFormatter = new \NumberFormatter('en_CA', \NumberFormatter::CURRENCY);

        return new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());
    }

    /**
     * @param self $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this->money->equals($other->money);
    }
}

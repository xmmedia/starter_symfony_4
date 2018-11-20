<?php

declare(strict_types=1);

namespace App\Model;

use Money\Currency;
use Money\Money;

final class MoneyFactory
{
    private static $currency = 'CAD';

    public static function fromInt(int $cents): Money
    {
        return new Money($cents, new Currency(self::$currency));
    }

    public static function zero(): Money
    {
        return self::fromInt(0);
    }
}

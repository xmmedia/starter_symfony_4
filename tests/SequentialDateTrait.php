<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\Date;

trait SequentialDateTrait
{
    private function startDate(): Date
    {
        return Date::fromString(
            $this->faker()
                ->dateTimeBetween('-5 years', '-1 year')
                ->format('Y-m-d')
        );
    }

    private function endDate(): Date
    {
        return Date::fromString(
            $this->faker()
                ->dateTimeBetween('-1 years', 'now')
                ->format('Y-m-d')
        );
    }
}

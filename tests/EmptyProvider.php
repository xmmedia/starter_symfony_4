<?php

declare(strict_types=1);

namespace App\Tests;

trait EmptyProvider
{
    public static function emptyProvider(): \Generator
    {
        yield [''];
        yield [' '];
        yield ['   '];
        yield [null];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests;

trait EmptyProvider
{
    public function emptyProvider(): \Generator
    {
        yield [''];
        yield [' '];
        yield ['   '];
        yield [null];
    }
}

<?php

declare(strict_types=1);

namespace App\Commanding;

/**
 * @see https://github.com/prooph/common
 */
interface PayloadConstructable
{
    public function __construct(array $payload = []);
}

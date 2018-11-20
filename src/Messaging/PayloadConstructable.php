<?php

declare(strict_types=1);

namespace App\Messaging;

interface PayloadConstructable
{
    public function __construct(array $payload = []);
}

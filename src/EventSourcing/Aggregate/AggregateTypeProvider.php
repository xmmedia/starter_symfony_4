<?php

declare(strict_types=1);

namespace App\EventSourcing\Aggregate;

interface AggregateTypeProvider
{
    public function aggregateType(): AggregateType;
}

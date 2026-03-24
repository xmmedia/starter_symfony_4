<?php

declare(strict_types=1);

namespace App\Projection\AuthLog;

use Xm\SymfonyBundle\Util\Filters;

class AuthLogFilters extends Filters
{
    public const string EVENT_TYPES = 'eventTypes';
    public const string DATE_FROM = 'dateFrom';
    public const string DATE_TO = 'dateTo';
    public const string Q = 'q';
    public const string OFFSET = 'offset';

    #[\Override]
    protected function parseFilters(array $filters): array
    {
        return $filters;
    }
}

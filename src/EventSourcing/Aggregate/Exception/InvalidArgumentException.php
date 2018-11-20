<?php

declare(strict_types=1);

namespace App\EventSourcing\Aggregate\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements AggregateException
{
}

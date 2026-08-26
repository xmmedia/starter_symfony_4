<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

final class AdminChangedPassword extends AggregateChanged
{
    use UserEventTrait;
}

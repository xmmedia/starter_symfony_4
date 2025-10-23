<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

final class UserDeactivatedByAdmin extends AggregateChanged
{
    use UserEventTrait;
}

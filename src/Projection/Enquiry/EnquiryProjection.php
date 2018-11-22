<?php

declare(strict_types=1);

namespace App\Projection\Enquiry;

use App\Model\Enquiry\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

class EnquiryProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $projector->fromStream('enquiry')
            ->when([
                Event\EnquiryWasSubmitted::class => function (
                    array $state,
                    Event\EnquiryWasSubmitted $event
                ): void {
                    /** @var EnquiryReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'id'        => $event->aggregateId(),
                        'name'      => $event->name(),
                        'email'     => $event->email()->toString(),
                        'message'   => $event->message(),
                        'submitted' => $event->createdAt(),
                    ], [
                        'submitted' => 'datetime',
                    ]);
                },
            ]);

        return $projector;
    }
}

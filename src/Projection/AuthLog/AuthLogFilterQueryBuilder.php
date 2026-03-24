<?php

declare(strict_types=1);

namespace App\Projection\AuthLog;

use Xm\SymfonyBundle\Doctrine\FilterQueryBuilder;
use Xm\SymfonyBundle\Util\FiltersInterface;

class AuthLogFilterQueryBuilder extends FilterQueryBuilder
{
    protected string $order = 'a.occurred_at DESC';

    public function queryParts(AuthLogFilters|FiltersInterface $filters): array
    {
        if ($filters->applied(AuthLogFilters::EVENT_TYPES)) {
            $eventTypes = $filters->get(AuthLogFilters::EVENT_TYPES);
            $eventTypeClauses = [];

            foreach ($eventTypes as $i => $eventType) {
                $eventTypeClauses[] = \sprintf(':eventType%d', $i);
                $this->parameters['eventType'.$i] = $eventType;
            }

            $this->whereClauses[] = \sprintf('a.event_type IN (%s)', implode(', ', $eventTypeClauses));
        }

        if ($filters->applied(AuthLogFilters::DATE_FROM)) {
            $this->whereClauses[] = 'a.occurred_at >= :dateFrom';
            $this->parameters['dateFrom'] = $filters->get(AuthLogFilters::DATE_FROM);
        }

        if ($filters->applied(AuthLogFilters::DATE_TO)) {
            $this->whereClauses[] = 'a.occurred_at <= :dateTo';
            $this->parameters['dateTo'] = $filters->get(AuthLogFilters::DATE_TO);
        }

        if ($filters->applied(AuthLogFilters::Q)) {
            $this->whereClauses[] = 'a.email LIKE :q';
            $this->parameters['q'] = '%'.$filters->get(AuthLogFilters::Q).'%';
        }

        return [
            'join'           => implode(' ', $this->joins),
            'where'          => implode(' AND ', $this->whereClauses),
            'order'          => $this->order,
            'parameters'     => $this->parameters,
            'parameterTypes' => $this->parameterTypes,
        ];
    }
}

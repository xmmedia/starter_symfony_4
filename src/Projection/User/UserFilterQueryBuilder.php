<?php

declare(strict_types=1);

namespace App\Projection\User;

use Xm\SymfonyBundle\Doctrine\FilterQueryBuilder;
use Xm\SymfonyBundle\Util\FiltersInterface;

class UserFilterQueryBuilder extends FilterQueryBuilder
{
    #[\Override]
    protected string $order = 'u.first_name ASC, u.last_name ASC, u.email ASC';

    public function queryParts(UserFilters|FiltersInterface $filters): array
    {
        if ($filters->applied(UserFilters::Q)) {
            $this->applyBasicQ($filters, UserFilters::Q, ['u.email', 'u.first_name', 'u.last_name']);

            // a search of more than 1 word may be a full name ("first last"),
            // so put the users whose full name starts with it first
            $words = preg_split('/\s+/', $filters->get(UserFilters::Q));
            if (\count($words) > 1) {
                $this->order = 'CONCAT_WS(\' \', u.first_name, u.last_name) LIKE :fullName DESC, '.$this->order;
                $this->parameters['fullName'] = implode(' ', $words).'%';
            }
        }

        if ($filters->applied(UserFilters::EMAIL)) {
            $this->whereClauses[] = 'u.email LIKE :email';
            $this->parameters['email'] = '%'.$filters->get(UserFilters::EMAIL).'%';
        }

        if ($filters->applied(UserFilters::EMAIL_EXACT)) {
            $this->whereClauses[] = 'u.email LIKE :email';
            $this->parameters['email'] = $filters->get(UserFilters::EMAIL_EXACT);
        }

        if ($filters->applied(UserFilters::ACTIVE)) {
            if ($filters->isTrue($filters->get(UserFilters::ACTIVE))) {
                $this->whereClauses[] = 'u.active = true';
                $this->whereClauses[] = 'u.verified = true';
            } else {
                $this->whereClauses[] = '(u.active = false OR u.verified = false)';
            }
        }

        if ($filters->applied(UserFilters::ACCOUNT_STATUS)) {
            switch ($filters->get(UserFilters::ACCOUNT_STATUS)) {
                case 'ACTIVE':
                    $this->whereClauses[] = 'u.active = true';
                    $this->whereClauses[] = 'u.verified = true';
                    break;
                case 'INACTIVE':
                    $this->whereClauses[] = '(u.active = false OR u.verified = false)';
                    break;
            }
        }

        if ($filters->applied(UserFilters::ROLES)) {
            $roleQueries = [];

            foreach ($filters->get(UserFilters::ROLES) as $i => $role) {
                $roleQueries[] = \sprintf('JSON_CONTAINS(u.roles, :role%d) = 1', $i);
                $this->parameters['role'.$i] = \sprintf('"%s"', $role->value);
            }

            $this->whereClauses[] = '('.implode(' OR ', $roleQueries).')';
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

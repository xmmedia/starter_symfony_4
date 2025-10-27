<?php

declare(strict_types=1);

namespace App\Projection\User;

use Xm\SymfonyBundle\Util\Filters;

class UserFilters extends Filters
{
    /** @var string General query, queries text fields on user. */
    public const Q = 'q';
    public const ACCOUNT_STATUS = 'accountStatus';
    public const ACTIVE = 'active';
    public const ROLES = 'roles';
    public const EMAIL = 'email';
    public const EMAIL_EXACT = 'emailExact';
    public const OFFSET = 'offset';

    #[\Override]
    protected function parseFilters(array $filters): array
    {
        if (\array_key_exists(self::ACTIVE, $filters)) {
            $filters[self::ACTIVE] = $this->isTrue($filters[self::ACTIVE]);
        }

        if (\array_key_exists(self::ACCOUNT_STATUS, $filters)) {
            if (null !== $filters[self::ACCOUNT_STATUS] && \in_array(strtoupper($filters[self::ACCOUNT_STATUS]), ['ACTIVE', 'INACTIVE'], true)) {
                $filters[self::ACCOUNT_STATUS] = strtoupper($filters[self::ACCOUNT_STATUS]);
            } else {
                unset($filters[self::ACCOUNT_STATUS]);
            }
        }

        return $filters;
    }
}

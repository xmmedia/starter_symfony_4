<?php

declare(strict_types=1);

namespace App\Projection\User;

use Xm\SymfonyBundle\Util\Filters;

class UserFilters extends Filters
{
    /** @var string General query, queries text fields on user. */
    public const string Q = 'q';
    public const string ACCOUNT_STATUS = 'accountStatus';
    public const string ACTIVE = 'active';
    public const string ROLES = 'roles';
    public const string EMAIL = 'email';
    public const string EMAIL_EXACT = 'emailExact';
    public const string OFFSET = 'offset';

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

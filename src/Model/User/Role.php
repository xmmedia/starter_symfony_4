<?php

declare(strict_types=1);

namespace App\Model\User;

use Xm\SymfonyBundle\Model\ValueObjectEnum;
use Xm\SymfonyBundle\Model\ValueObjectEnumTrait;

/**
 * Also in Pinia & security.yaml.
 */
enum Role: string implements ValueObjectEnum
{
    use ValueObjectEnumTrait;

    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    /** Not available for users, only for security to allow switching to another user. */
    case ROLE_ALLOWED_TO_SWITCH = 'ROLE_ALLOWED_TO_SWITCH';
}

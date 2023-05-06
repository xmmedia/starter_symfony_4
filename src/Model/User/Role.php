<?php

declare(strict_types=1);

namespace App\Model\User;

use Xm\SymfonyBundle\Model\Enum;

/**
 * Also in Pinia & security.yaml.
 *
 * @method static Role ROLE_USER();
 * @method static Role ROLE_ADMIN();
 * @method static Role ROLE_SUPER_ADMIN();
 */
final class Role extends Enum
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
}

<?php

declare(strict_types=1);

namespace App\Model\User;

/**
 * Also in Vuex & security.yaml.
 *
 * @method static Role ROLE_USER();
 * @method static Role ROLE_ADMIN();
 * @method static Role ROLE_SUPER_ADMIN();
 */
class Role extends \Xm\SymfonyBundle\Model\User\Role
{
}

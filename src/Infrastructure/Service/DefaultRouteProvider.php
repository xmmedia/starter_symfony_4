<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Security\Security;

readonly class DefaultRouteProvider
{
    public function __construct(private Security $security)
    {
    }

    public function __invoke(): array
    {
        // @todo-symfony
        if ($this->security->isLoggedIn()) {
            if ($this->security->hasAdminRole()) {
                return ['admin_default'];
            }

            return ['user_default', ['path' => 'dashboard']];
        }

        return ['app_login'];
    }
}

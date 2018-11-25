<?php

declare(strict_types=1);

namespace App\Model\Auth;

interface AuthList
{
    public function save(Auth $auth): void;

    public function get(AuthId $authId): ?Auth;
}

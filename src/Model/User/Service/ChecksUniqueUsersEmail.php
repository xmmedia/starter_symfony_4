<?php

declare(strict_types=1);

namespace App\Model\User\Service;

use App\Model\Email;
use App\Model\User\UserId;

interface ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserId;
}

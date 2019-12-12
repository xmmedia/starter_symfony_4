<?php

declare(strict_types=1);

namespace App\Model\User\Service;

use App\Model\User\UserIdInterface;
use Xm\SymfonyBundle\Model\Email;

interface ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface;
}

<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Token;

interface TokenGeneratorInterface
{
    public function __invoke(): Token;
}

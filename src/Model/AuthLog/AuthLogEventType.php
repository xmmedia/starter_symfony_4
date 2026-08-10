<?php

declare(strict_types=1);

namespace App\Model\AuthLog;

use Xm\SymfonyBundle\Model\ValueObjectEnum;
use Xm\SymfonyBundle\Model\ValueObjectEnumTrait;

enum AuthLogEventType: string implements ValueObjectEnum
{
    use ValueObjectEnumTrait;

    case LOGIN = 'LOGIN';
    case LOGIN_FAILED = 'LOGIN_FAILED';
    case IMPERSONATION_STARTED = 'IMPERSONATION_STARTED';
    case IMPERSONATION_ENDED = 'IMPERSONATION_ENDED';
}

<?php

declare(strict_types=1);

namespace App\Model\AuthLog;

use Xm\SymfonyBundle\Model\ValueObjectEnum;
use Xm\SymfonyBundle\Model\ValueObjectEnumTrait;

enum AuthLogEventType: string implements ValueObjectEnum
{
    use ValueObjectEnumTrait;

    case LOGIN = 'login';
    case LOGIN_FAILED = 'login_failed';
    case IMPERSONATION_STARTED = 'impersonation_started';
    case IMPERSONATION_ENDED = 'impersonation_ended';
}

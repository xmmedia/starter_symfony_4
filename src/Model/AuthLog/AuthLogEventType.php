<?php

declare(strict_types=1);

namespace App\Model\AuthLog;

use Xm\SymfonyBundle\Model\Enum;

/**
 * @method static AuthLogEventType LOGIN()
 * @method static AuthLogEventType LOGIN_FAILED()
 * @method static AuthLogEventType IMPERSONATION_STARTED()
 * @method static AuthLogEventType IMPERSONATION_ENDED()
 */
final class AuthLogEventType extends Enum
{
    public const string LOGIN = 'login';
    public const string LOGIN_FAILED = 'login_failed';
    public const string IMPERSONATION_STARTED = 'impersonation_started';
    public const string IMPERSONATION_ENDED = 'impersonation_ended';
}

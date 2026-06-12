<?php

declare(strict_types=1);

namespace App\Projection;

class Table
{
    public const string AUTH_LOG = 'auth_log';
    public const string USER = 'user';
    public const string USER_TOTP = 'user_totp';

    public const int VARCHAR_MULTIBYTE_MAX_LENGTH = 191;
}

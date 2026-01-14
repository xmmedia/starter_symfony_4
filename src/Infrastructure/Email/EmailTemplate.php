<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

class EmailTemplate
{
    public const AUTH_USER_INVITE = 'auth-user_invite';
    public const AUTH_USER_VERIFICATION = 'auth-user_verification';
    public const AUTH_LOGIN_LINK = 'auth-login_link';
    public const AUTH_PASSWORD_RESET = 'auth-password_reset';
    public const USER_PROFILE_UPDATED = 'profile_updated';
    public const USER_PASSWORD_CHANGED = 'password_changed';
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

final readonly class EmailTemplate
{
    public const string AUTH_USER_INVITE = 'auth-user_invite';
    public const string AUTH_USER_VERIFICATION = 'auth-user_verification';
    public const string AUTH_LOGIN_LINK = 'auth-login_link';
    public const string AUTH_PASSWORD_RESET = 'auth-password_reset';
    public const string USER_PROFILE_UPDATED = 'profile_updated';
    public const string USER_PASSWORD_CHANGED = 'password_changed';
}

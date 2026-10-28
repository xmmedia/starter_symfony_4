<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

/**
 * The Postmark template aliases. app:postmark:check-templates requires Postmark to have every one.
 */
enum EmailTemplate: string
{
    case AUTH_USER_INVITE = 'auth-user_invite';
    case AUTH_USER_VERIFICATION = 'auth-user_verification';
    case AUTH_LOGIN_LINK = 'auth-login_link';
    case AUTH_PASSWORD_RESET = 'auth-password_reset';
    case USER_PROFILE_UPDATED = 'profile_updated';
    case USER_PASSWORD_CHANGED = 'password_changed';
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

class EmailTemplate
{
    // @todo-symfony
    public const PASSWORD_RESET = 'auth-password_reset';
    public const USER_INVITE = 'auth-user_invite';
}

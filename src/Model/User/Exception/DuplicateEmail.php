<?php

declare(strict_types=1);

namespace App\Model\User\Exception;

use App\Model\User\UserIdInterface;
use Xm\SymfonyBundle\Model\Email;

final class DuplicateEmail extends \InvalidArgumentException
{
    public static function withEmail(Email $email, UserIdInterface $userId): self
    {
        return new self(
            sprintf(
                'The email address "%s" is already used by user "%s".',
                $email,
                $userId,
            ),
        );
    }
}

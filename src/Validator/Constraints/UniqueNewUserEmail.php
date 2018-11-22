<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueNewUserEmail extends Constraint
{
    public $message = 'This email address has already been used.';
}

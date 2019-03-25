<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class PhoneNumber extends Constraint
{
    public $message = 'This phone number is invalid.';
}

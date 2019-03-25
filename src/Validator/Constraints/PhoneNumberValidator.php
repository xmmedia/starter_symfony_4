<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PhoneNumberValidator extends ConstraintValidator
{
    /**
     * @param string                 $value
     * @param Constraint|PhoneNumber $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        try {
            \App\Model\PhoneNumber::fromString($value);
        } catch (\InvalidArgumentException $e) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

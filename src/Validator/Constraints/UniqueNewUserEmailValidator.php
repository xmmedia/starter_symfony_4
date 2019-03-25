<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNewUserEmailValidator extends ConstraintValidator
{
    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmail;

    public function __construct(
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ) {
        $this->checksUniqueUsersEmail = $checksUniqueUsersEmail;
    }

    /**
     * @param string                        $email
     * @param Constraint|UniqueNewUserEmail $constraint
     */
    public function validate($email, Constraint $constraint): void
    {
        if (empty($email)) {
            return;
        }

        if (($this->checksUniqueUsersEmail)(Email::fromString($email))) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

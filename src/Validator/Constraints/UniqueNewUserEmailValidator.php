<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNewUserEmailValidator extends ConstraintValidator
{
    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmailAddress;

    public function __construct(
        ChecksUniqueUsersEmail $checksUniqueUsersEmailAddress
    ) {
        $this->checksUniqueUsersEmailAddress = $checksUniqueUsersEmailAddress;
    }

    /**
     * @param Email                              $email
     * @param Constraint|UniqueNewUserEmail $constraint
     */
    public function validate($email, Constraint $constraint): void
    {
        if (($this->checksUniqueUsersEmailAddress)($email)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

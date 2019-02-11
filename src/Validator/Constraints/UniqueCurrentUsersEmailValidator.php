<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueCurrentUsersEmailValidator extends ConstraintValidator
{
    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmailAddress;

    /** @var Security */
    private $security;

    public function __construct(
        ChecksUniqueUsersEmail $checksUniqueUsersEmailAddress,
        Security $security
    ) {
        $this->checksUniqueUsersEmailAddress = $checksUniqueUsersEmailAddress;
        $this->security = $security;
    }

    /**
     * @param Email                              $email
     * @param Constraint|UniqueCurrentUsersEmail $constraint
     */
    public function validate($email, Constraint $constraint): void
    {
        $userId = ($this->checksUniqueUsersEmailAddress)($email);

        if ($userId && !$this->security->getUser()->userId()->sameValueAs($userId)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

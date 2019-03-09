<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\User\Service\ChecksUniqueUsersEmail;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueExistingUserEmailValidator extends ConstraintValidator
{
    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmail;

    public function __construct(
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ) {
        $this->checksUniqueUsersEmail = $checksUniqueUsersEmail;
    }

    /**
     * @param array                              $data
     * @param Constraint|UniqueCurrentUsersEmail $constraint
     */
    public function validate($data, Constraint $constraint): void
    {
        $userId = ($this->checksUniqueUsersEmail)($data['email']);

        if ($userId && !$data['userId']->sameValueAs($userId)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('[email]')
                ->addViolation();
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueExistingUserEmailValidator extends ConstraintValidator
{
    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmailAddress;

    public function __construct(
        ChecksUniqueUsersEmail $checksUniqueUsersEmailAddress
    ) {
        $this->checksUniqueUsersEmailAddress = $checksUniqueUsersEmailAddress;
    }

    /**
     * @param array                              $data
     * @param Constraint|UniqueCurrentUsersEmail $constraint
     */
    public function validate($data, Constraint $constraint): void
    {
        $userId = ($this->checksUniqueUsersEmailAddress)($data['email']);

        if ($userId && !$data['id']->sameValueAs($userId)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('[email]')
                ->addViolation();
        }
    }
}

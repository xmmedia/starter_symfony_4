<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\Email;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Model\User\UserId;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
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
        try {
            $currentUserId = UserId::fromString($data['userId']);
        } catch (InvalidUuidStringException $e) {
            // just don't want the form to fail
            return;
        }

        $duplicateUserId = ($this->checksUniqueUsersEmail)(
            Email::fromString($data['email'])
        );

        if ($duplicateUserId && !$currentUserId->sameValueAs($duplicateUserId)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('[email]')
                ->addViolation();
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Model\User;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Email;
use App\Model\Entity;
use App\Model\NotificationGatewayId;
use App\Model\User\Service\ChecksUniqueUsersEmail;

class User extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    public const PASSWORD_MIN_LENGTH = 12;

    /** @var UserId */
    private $userId;

    /** @var bool */
    private $verified;

    /** @var bool */
    private $active;

    public static function addByAdmin(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $active,
        Name $firstName,
        Name $lastName,
        bool $sendInvite,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ): self {
        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            throw Exception\DuplicateEmail::withEmail(
                $email,
                $duplicateUserId
            );
        }

        // if they're not active, don't allow sending an invite
        if (!$active) {
            $sendInvite = false;
        }

        $self = new self();
        $self->recordThat(
            Event\UserWasAddedByAdmin::now(
                $userId,
                $email,
                $encodedPassword,
                $role,
                $active,
                $firstName,
                $lastName,
                $sendInvite
            )
        );

        return $self;
    }

    public static function addByAdminMinimum(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ): self {
        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            throw Exception\DuplicateEmail::withEmail(
                $email,
                $duplicateUserId
            );
        }

        $self = new self();
        $self->recordThat(
            Event\MinimalUserWasAddedByAdmin::now(
                $userId,
                $email,
                $encodedPassword,
                $role
            )
        );

        return $self;
    }

    public function updateByAdmin(
        Email $email,
        Role $role,
        Name $firstName,
        Name $lastName,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ): void {
        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            if (!$this->userId->sameValueAs($duplicateUserId)) {
                throw Exception\DuplicateEmail::withEmail(
                    $email,
                    $duplicateUserId
                );
            }
        }

        $this->recordThat(
            Event\UserWasUpdatedByAdmin::now(
                $this->userId,
                $email,
                $role,
                $firstName,
                $lastName
            )
        );
    }

    public function changePasswordByAdmin(string $encodedPassword): void
    {
        $this->recordThat(
            Event\AdminChangedPassword::now($this->userId, $encodedPassword)
        );
    }

    public function verifyByAdmin(): void
    {
        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToVerify($this->userId);
        }

        $this->recordThat(
            Event\UserVerifiedByAdmin::now($this->userId)
        );
    }

    public function activateByAdmin(): void
    {
        if ($this->active) {
            throw Exception\InvalidUserActiveStatus::triedToActivateWhenAlreadyActive(
                $this->userId
            );
        }

        $this->recordThat(
            Event\UserActivatedByAdmin::now($this->userId)
        );
    }

    public function deactivateByAdmin(): void
    {
        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToDeactivateWhenAlreadyInactive(
                $this->userId
            );
        }

        $this->recordThat(
            Event\UserDeactivatedByAdmin::now($this->userId)
        );
    }

    public function inviteSent(
        Token $token,
        NotificationGatewayId $messageId
    ): void {
        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToSendVerification(
                $this->userId
            );
        }

        $this->recordThat(
            Event\InviteSent::now($this->userId, $token, $messageId)
        );
    }

    public function verify(): void
    {
        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToVerify($this->userId);
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToVerifyAnInactiveUser(
                $this->userId
            );
        }

        $this->recordThat(
            Event\UserVerified::now($this->userId)
        );
    }

    public function passwordRecoverySent(
        Token $token,
        NotificationGatewayId $messageId
    ): void {
        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToRequestPasswordReset(
                $this->userId
            );
        }

        $this->recordThat(
            Event\PasswordRecoverySent::now($this->userId, $token, $messageId)
        );
    }

    public function update(
        Email $email,
        Name $firstName,
        Name $lastName,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ): void {
        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToUpdateProfile(
                $this->userId
            );
        }

        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            if (!$this->userId->sameValueAs($duplicateUserId)) {
                throw Exception\DuplicateEmail::withEmail(
                    $email,
                    $duplicateUserId
                );
            }
        }

        $this->recordThat(
            Event\UserUpdatedProfile::now(
                $this->userId,
                $email,
                $firstName,
                $lastName
            )
        );
    }

    public function loggedIn(): void
    {
        if (!$this->verified) {
            throw Exception\UserNotVerified::triedToLogin($this->userId);
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToLogin(
                $this->userId
            );
        }

        $this->recordThat(Event\UserLoggedIn::now($this->userId));
    }

    public function changePassword(string $encodedPassword): void
    {
        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToChangePassword(
                $this->userId
            );
        }

        $this->recordThat(
            Event\ChangedPassword::now($this->userId, $encodedPassword)
        );
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function verified(): bool
    {
        return $this->verified;
    }

    public function active(): bool
    {
        return $this->active;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function aggregateId(): string
    {
        return $this->userId->toString();
    }

    protected function whenUserWasAddedByAdmin(
        Event\UserWasAddedByAdmin $event
    ): void {
        $this->userId = $event->userId();
        $this->verified = !$event->sendInvite();
        $this->active = $event->active();
    }

    protected function whenMinimalUserWasAddedByAdmin(
        Event\MinimalUserWasAddedByAdmin $event
    ): void {
        $this->userId = $event->userId();
        $this->verified = true;
        $this->active = true;
    }

    protected function whenUserWasUpdatedByAdmin(Event\UserWasUpdatedByAdmin $event): void
    {
        // noop
    }

    protected function whenAdminChangedPassword(
        Event\AdminChangedPassword $event
    ): void {
        // noop
    }

    protected function whenUserVerifiedByAdmin(
        Event\UserVerifiedByAdmin $event
    ): void {
        $this->verified = true;
    }

    protected function whenUserActivatedByAdmin(
        Event\UserActivatedByAdmin $event
    ): void {
        $this->active = true;
    }

    protected function whenUserDeactivatedByAdmin(
        Event\UserDeactivatedByAdmin $event
    ): void {
        $this->active = false;
    }

    protected function whenInviteSent(Event\InviteSent $event): void
    {
        // noop
    }

    protected function whenUserVerified(Event\UserVerified $event): void
    {
        $this->verified = true;
    }

    protected function whenPasswordRecoverySent(
        Event\PasswordRecoverySent $event
    ): void {
        // noop
    }

    protected function whenUserUpdatedProfile(
        Event\UserUpdatedProfile $event
    ): void {
        // noop
    }

    protected function whenChangedPassword(Event\ChangedPassword $event): void
    {
        // noop
    }

    protected function whenUserLoggedIn(Event\UserLoggedIn $event): void
    {
        // noop
    }

    /**
     * @param User|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (\get_class($this) !== \get_class($other)) {
            return false;
        }

        return $this->userId->sameValueAs($other->userId);
    }
}

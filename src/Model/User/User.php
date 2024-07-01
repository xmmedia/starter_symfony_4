<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\Entity;
use Xm\SymfonyBundle\Model\NotificationGatewayId;

class User extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    public const PASSWORD_MIN_LENGTH = 12;

    private UserId $userId;
    private bool $verified = false;
    private bool $active = false;
    private bool $deleted = false;

    public static function addByAdmin(
        UserId $userId,
        Email $email,
        string $hashedPassword,
        Role $role,
        bool $active,
        Name $firstName,
        Name $lastName,
        bool $sendInvite,
        UserData $userData,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ): self {
        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            throw Exception\DuplicateEmail::withEmail($email, $duplicateUserId);
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
                $hashedPassword,
                $role,
                $active,
                $firstName,
                $lastName,
                $sendInvite,
                $userData,
            ),
        );

        return $self;
    }

    public static function addByAdminMinimum(
        UserId $userId,
        Email $email,
        string $hashedPassword,
        Role $role,
        Name $firstName,
        Name $lastName,
        bool $sendInvite,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ): self {
        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            throw Exception\DuplicateEmail::withEmail($email, $duplicateUserId);
        }

        $self = new self();
        $self->recordThat(
            Event\MinimalUserWasAddedByAdmin::now(
                $userId,
                $email,
                $hashedPassword,
                $role,
                $firstName,
                $lastName,
                $sendInvite,
            ),
        );

        return $self;
    }

    public function updateByAdmin(
        Email $email,
        Role $role,
        Name $firstName,
        Name $lastName,
        UserData $userData,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ): void {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'update (by admin)');
        }

        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            if (!$this->userId->sameValueAs($duplicateUserId)) {
                throw Exception\DuplicateEmail::withEmail($email, $duplicateUserId);
            }
        }

        $this->recordThat(
            Event\UserWasUpdatedByAdmin::now(
                $this->userId,
                $email,
                $role,
                $firstName,
                $lastName,
                $userData,
            ),
        );
    }

    public function changePasswordByAdmin(string $hashedPassword): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'change password (by admin)');
        }

        $this->recordThat(
            Event\AdminChangedPassword::now($this->userId, $hashedPassword),
        );
    }

    public function verifyByAdmin(): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'verify (by admin)');
        }

        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToVerify($this->userId);
        }

        $this->recordThat(
            Event\UserVerifiedByAdmin::now($this->userId),
        );
    }

    public function activateByAdmin(): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'activate (by admin)');
        }

        if ($this->active) {
            throw Exception\InvalidUserActiveStatus::triedToActivateWhenAlreadyActive($this->userId);
        }

        $this->recordThat(
            Event\UserActivatedByAdmin::now($this->userId),
        );
    }

    public function deactivateByAdmin(): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'deactivate (by admin)');
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToDeactivateWhenAlreadyInactive($this->userId);
        }

        $this->recordThat(
            Event\UserDeactivatedByAdmin::now($this->userId),
        );
    }

    public function inviteSent(NotificationGatewayId $messageId): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'send invite to');
        }

        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToSendVerification($this->userId);
        }

        $this->recordThat(
            Event\InviteSent::now($this->userId, $messageId),
        );
    }

    public function verificationSent(NotificationGatewayId $messageId): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'send invite to');
        }

        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToSendVerification($this->userId);
        }

        $this->recordThat(
            Event\VerificationSent::now($this->userId, $messageId),
        );
    }

    public function verify(): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'verify');
        }

        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToVerify($this->userId);
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToVerifyAnInactiveUser($this->userId);
        }

        $this->recordThat(
            Event\UserVerified::now($this->userId),
        );
    }

    public function activate(): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'activate');
        }

        if ($this->verified) {
            throw Exception\UserAlreadyVerified::triedToActivate($this->userId);
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToActivateAnInactiveUser($this->userId);
        }

        $this->recordThat(
            Event\UserActivated::now($this->userId),
        );
    }

    public function passwordRecoverySent(NotificationGatewayId $messageId): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'send password recovery to');
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToRequestPasswordReset($this->userId);
        }

        $this->recordThat(
            Event\PasswordRecoverySent::now($this->userId, $messageId),
        );
    }

    public function update(
        Email $email,
        Name $firstName,
        Name $lastName,
        UserData $userData,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail,
    ): void {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'update');
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToUpdateProfile($this->userId);
        }

        if ($duplicateUserId = $checksUniqueUsersEmail($email)) {
            if (!$this->userId->sameValueAs($duplicateUserId)) {
                throw Exception\DuplicateEmail::withEmail($email, $duplicateUserId);
            }
        }

        $this->recordThat(
            Event\UserUpdatedProfile::now(
                $this->userId,
                $email,
                $firstName,
                $lastName,
                $userData,
            ),
        );
    }

    public function changePassword(string $hashedPassword): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'change password');
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToChangePassword($this->userId);
        }

        $this->recordThat(
            Event\ChangedPassword::now($this->userId, $hashedPassword),
        );
    }

    public function upgradePassword(string $hashedPassword): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedTo($this->userId, 'upgrade password');
        }

        if (!$this->active) {
            throw Exception\InvalidUserActiveStatus::triedToUpgradePassword($this->userId);
        }

        $this->recordThat(
            Event\PasswordUpgraded::now($this->userId, $hashedPassword),
        );
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw Exception\UserIsDeleted::triedToDelete($this->userId);
        }

        $this->recordThat(
            Event\UserWasDeletedByAdmin::now($this->userId),
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
        Event\UserWasAddedByAdmin $event,
    ): void {
        $this->userId = $event->userId();
        $this->verified = !$event->sendInvite();
        $this->active = $event->active();
    }

    protected function whenMinimalUserWasAddedByAdmin(
        Event\MinimalUserWasAddedByAdmin $event,
    ): void {
        $this->userId = $event->userId();
        $this->verified = !$event->sendInvite();
        $this->active = true;
    }

    protected function whenUserWasUpdatedByAdmin(Event\UserWasUpdatedByAdmin $event): void
    {
        // noop
    }

    protected function whenAdminChangedPassword(Event\AdminChangedPassword $event): void
    {
        // noop
    }

    protected function whenUserVerifiedByAdmin(Event\UserVerifiedByAdmin $event): void
    {
        $this->verified = true;
    }

    protected function whenUserActivated(Event\UserActivated $event): void
    {
        $this->verified = true;
    }

    protected function whenUserActivatedByAdmin(Event\UserActivatedByAdmin $event): void
    {
        $this->active = true;
    }

    protected function whenUserDeactivatedByAdmin(Event\UserDeactivatedByAdmin $event): void
    {
        $this->active = false;
    }

    protected function whenInviteSent(Event\InviteSent $event): void
    {
        // noop
    }

    protected function whenVerificationSent(Event\VerificationSent $event): void
    {
        // noop
    }

    protected function whenUserVerified(Event\UserVerified $event): void
    {
        $this->verified = true;
    }

    protected function whenPasswordRecoverySent(Event\PasswordRecoverySent $event): void
    {
        // noop
    }

    protected function whenUserUpdatedProfile(Event\UserUpdatedProfile $event): void
    {
        // noop
    }

    protected function whenChangedPassword(Event\ChangedPassword $event): void
    {
        // noop
    }

    protected function whenPasswordUpgraded(Event\PasswordUpgraded $event): void
    {
        // noop
    }

    protected function whenUserWasDeletedByAdmin(Event\UserWasDeletedByAdmin $event): void
    {
        $this->deleted = true;
    }

    /**
     * @param User $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this->userId->sameValueAs($other->userId);
    }
}

<?php

declare(strict_types=1);

namespace App\Model\User;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Email;
use App\Model\EmailGatewayMessageId;
use App\Model\Entity;
use Symfony\Component\Security\Core\Role\Role;

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

    public static function createByAdmin(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $active,
        Name $firstName,
        Name $lastName,
        bool $sendInvite
    ): self {
        // if they're not active, don't allow sending an invite
        if (!$active) {
            $sendInvite = false;
        }

        $self = new self();
        $self->recordThat(
            Event\UserWasCreatedByAdmin::now(
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

    public static function createByAdminMinimum(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role
    ): self {
        $self = new self();
        $self->recordThat(
            Event\MinimalUserWasCreatedByAdmin::now(
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
        Name $lastName
    ): void {
        $this->recordThat(
            Event\AdminUpdatedUser::now(
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

    public function inviteSent(Token $token, EmailGatewayMessageId $messageId): void
    {
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
        EmailGatewayMessageId $messageId
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

    public function updateFromProfile(
        Email $email,
        Name $firstName,
        Name $lastName
    ): void {
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
        $this->recordThat(Event\UserLoggedIn::now($this->userId));
    }

    public function changePassword(string $encodedPassword): void
    {
        $this->recordThat(Event\ChangedPassword::now($this->userId, $encodedPassword));
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

    protected function aggregateId(): string
    {
        return $this->userId->toString();
    }

    protected function whenUserWasCreatedByAdmin(Event\UserWasCreatedByAdmin $event): void
    {
        $this->userId = $event->userId();
        $this->verified = !$event->sendInvite();
        $this->active = true;
    }

    protected function whenMinimalUserWasCreatedByAdmin(Event\MinimalUserWasCreatedByAdmin $event): void
    {
        $this->userId = $event->userId();
        $this->verified = true;
        $this->active = true;
    }

    protected function whenAdminUpdatedUser(Event\AdminUpdatedUser $event): void
    {
        // nothing atm
    }

    protected function whenAdminChangedPassword(Event\AdminChangedPassword $event): void
    {
        // nothing atm
    }

    protected function whenUserVerifiedByAdmin(Event\UserVerifiedByAdmin $event): void
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
        // nothing atm
    }

    protected function whenUserVerified(Event\UserVerified $event): void
    {
        $this->verified = true;
    }

    protected function whenPasswordRecoverySent(Event\PasswordRecoverySent $event): void
    {
        // nothing atm
    }

    protected function whenUserUpdatedProfile(Event\UserUpdatedProfile $event): void
    {
        // nothing atm
    }

    protected function whenChangedPassword(Event\ChangedPassword $event): void
    {
        // nothing atm
    }

    protected function whenUserLoggedIn(Event\UserLoggedIn $event): void
    {
        // nothing atm
    }

    /**
     * @param User|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        return get_class($this) === get_class($other) && $this->userId->sameValueAs($other->userId);
    }
}

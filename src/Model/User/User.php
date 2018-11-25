<?php

declare(strict_types=1);

namespace App\Model\User;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Email;
use App\Model\Entity;
use App\Model\User\Event\UserActivatedByAdmin;
use App\Model\User\Event\AdminChangedPassword;
use App\Model\User\Event\AdminUpdatedUser;
use App\Model\User\Event\ChangedPassword;
use App\Model\User\Event\MinimalUserWasCreatedByAdmin;
use App\Model\User\Event\UserDeactivatedByAdmin;
use App\Model\User\Event\UserLoggedIn;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\Event\UserWasCreatedByAdmin;
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
        Name $lastName
    ): self {
        $self = new self();
        $self->recordThat(
            UserWasCreatedByAdmin::now(
                $userId,
                $email,
                $encodedPassword,
                $role,
                $active,
                $firstName,
                $lastName
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
            MinimalUserWasCreatedByAdmin::now(
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
            AdminUpdatedUser::now(
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
            AdminChangedPassword::now($this->userId, $encodedPassword)
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
            UserActivatedByAdmin::now($this->userId)
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
            UserDeactivatedByAdmin::now($this->userId)
        );
    }

    public function updateFromProfile(
        Email $email,
        Name $firstName,
        Name $lastName
    ): void {
        $this->recordThat(
            UserUpdatedProfile::now(
                $this->userId,
                $email,
                $firstName,
                $lastName
            )
        );
    }

    public function loggedIn(): void
    {
        $this->recordThat(UserLoggedIn::now($this->userId));
    }

    public function changePassword(string $encodedPassword): void
    {
        $this->recordThat(ChangedPassword::now($this->userId, $encodedPassword));
    }

    protected function aggregateId(): string
    {
        return $this->userId->toString();
    }

    protected function whenUserWasCreatedByAdmin(UserWasCreatedByAdmin $event): void
    {
        $this->userId = $event->userId();
        $this->verified = true;
        $this->active = true;
    }

    protected function whenMinimalUserWasCreatedByAdmin(MinimalUserWasCreatedByAdmin $event): void
    {
        $this->userId = $event->userId();
        $this->verified = true;
        $this->active = true;
    }

    protected function whenAdminUpdatedUser(AdminUpdatedUser $event): void
    {
        // nothing atm
    }

    protected function whenAdminChangedPassword(AdminChangedPassword $event): void
    {
        // nothing atm
    }

    protected function whenUserActivatedByAdmin(UserActivatedByAdmin $event): void
    {
        $this->active = true;
    }

    protected function whenUserDeactivatedByAdmin(UserDeactivatedByAdmin $event): void
    {
        $this->active = false;
    }

    protected function whenUserUpdatedProfile(UserUpdatedProfile $event): void
    {
        // nothing atm
    }

    protected function whenChangedPassword(ChangedPassword $event): void
    {
        // nothing atm
    }

    protected function whenUserLoggedIn(UserLoggedIn $event): void
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

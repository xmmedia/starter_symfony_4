<?php

declare(strict_types=1);

namespace App\Model\User;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Email;
use App\Model\Entity;
use App\Model\User\Event\AdminChangedPassword;
use App\Model\User\Event\AdminUpdatedUser;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\Event\UserWasCreatedByAdmin;
use Symfony\Component\Security\Core\Role\Role;

class User extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    public const PASSWORD_MIN_LENGTH = 12;

    /** @var UserId */
    private $userId;

    public static function createByAdmin(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $enabled,
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
                $enabled,
                $firstName,
                $lastName
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

    protected function aggregateId(): string
    {
        return $this->userId->toString();
    }

    protected function whenUserWasCreatedByAdmin(UserWasCreatedByAdmin $event): void
    {
        $this->userId = $event->userId();
    }

    protected function whenAdminUpdatedUser(AdminUpdatedUser $event): void
    {
        // nothing atm
    }

    protected function whenAdminChangedPassword(AdminChangedPassword $event): void
    {
        // nothing atm
    }

    protected function whenUserUpdatedProfile(UserUpdatedProfile $event): void
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

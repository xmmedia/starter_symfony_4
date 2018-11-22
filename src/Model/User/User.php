<?php

declare(strict_types=1);

namespace App\Model\User;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Email;
use App\Model\Entity;
use App\Model\User\Event\UserWasCreatedByAdmin;
use Symfony\Component\Security\Core\Role\Role;

class User extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    public const NAME_MIN_LENGTH = 5;
    public const NAME_MAX_LENGTH = 50;

    public const PASSWORD_MIN_LENGTH = 12;

    /** @var UserId */
    private $userId;

    public static function createByAdmin(
        UserId $userId,
        Email $email,
        string $encodedPassword,
        Role $role,
        bool $enabled,
        string $firstName,
        string $lastName
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

    protected function aggregateId(): string
    {
        return $this->userId->toString();
    }

    protected function whenUserWasCreatedByAdmin(UserWasCreatedByAdmin $event): void
    {
        $this->userId = $event->userId();
    }

    /**
     * @param User|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        return get_class($this) === get_class($other) && $this->userId->sameValueAs($other->userId);
    }
}

<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Model\User\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\Util\Utils;

/**
 * @method \Prooph\EventStore\Projection\ReadModel readModel()
 */
class UserProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $types = [
            'verified'  => 'boolean',
            'active'    => 'boolean',
            'roles'     => 'json',
            'user_data' => 'json',
        ];

        $projector->fromStream('user')
            ->when([
                Event\UserWasAddedByAdmin::class => function (
                    array $state,
                    Event\UserWasAddedByAdmin $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'user_id'    => $event->aggregateId(),
                        'email'      => mb_strtolower($event->email()->toString()),
                        'password'   => $event->hashedPassword(),
                        // if sent an invitation, then account is not verified
                        // if they didn't send an invitation, then account is verified
                        // because there's no way for them to verify the account
                        'verified'   => !$event->sendInvite(),
                        'active'     => $event->active(),
                        'roles'      => [$event->role()->getValue()],
                        'first_name' => $event->firstName()->toString(),
                        'last_name'  => $event->lastName()->toString(),
                        'user_data'  => $event->userData()->toArray(),
                    ], $types);
                },

                Event\MinimalUserWasAddedByAdmin::class => function (
                    array $state,
                    Event\MinimalUserWasAddedByAdmin $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'user_id'    => $event->aggregateId(),
                        'email'      => mb_strtolower($event->email()->toString()),
                        'password'   => $event->hashedPassword(),
                        'verified'   => !$event->sendInvite(),
                        'active'     => true,
                        'roles'      => [$event->role()->getValue()],
                        'first_name' => Utils::serialize($event->firstName()),
                        'last_name'  => Utils::serialize($event->lastName()),
                    ], $types);
                },

                Event\UserWasUpdatedByAdmin::class => function (
                    array $state,
                    Event\UserWasUpdatedByAdmin $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'email'      => mb_strtolower($event->email()->toString()),
                            'roles'      => [$event->role()->getValue()],
                            'first_name' => $event->firstName()->toString(),
                            'last_name'  => $event->lastName()->toString(),
                            'user_data'  => $event->userData()->toArray(),
                        ],
                        $types,
                    );
                },

                Event\AdminChangedPassword::class => function (
                    array $state,
                    Event\AdminChangedPassword $event,
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'password' => $event->hashedPassword(),
                        ],
                    );
                },

                Event\UserVerifiedByAdmin::class => function (
                    array $state,
                    Event\UserVerifiedByAdmin $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'verified' => true,
                        ],
                        $types,
                    );
                },

                Event\UserActivatedByAdmin::class => function (
                    array $state,
                    Event\UserActivatedByAdmin $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'active' => true,
                        ],
                        $types,
                    );
                },

                Event\UserDeactivatedByAdmin::class => function (
                    array $state,
                    Event\UserDeactivatedByAdmin $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'active' => false,
                        ],
                        $types,
                    );
                },

                Event\UserUpdatedProfile::class => function (
                    array $state,
                    Event\UserUpdatedProfile $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'email'      => mb_strtolower($event->email()->toString()),
                            'first_name' => $event->firstName()->toString(),
                            'last_name'  => $event->lastName()->toString(),
                            'user_data'  => $event->userData()->toArray(),
                        ],
                        $types,
                    );
                },

                Event\ChangedPassword::class => function (
                    array $state,
                    Event\ChangedPassword $event,
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'password' => $event->hashedPassword(),
                        ],
                    );
                },

                Event\PasswordUpgraded::class => function (
                    array $state,
                    Event\PasswordUpgraded $event,
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'password' => $event->hashedPassword(),
                        ],
                    );
                },

                Event\UserVerified::class  => function (
                    array $state,
                    Event\UserVerified $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'verified' => true,
                        ],
                        $types,
                    );
                },
                Event\UserActivated::class => function (
                    array $state,
                    Event\UserActivated $event,
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'verified' => true,
                        ],
                        $types,
                    );
                },

                Event\UserWasDeletedByAdmin::class => function (
                    array $state,
                    Event\UserWasDeletedByAdmin $event,
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'remove',
                        $event->userId()->toString(),
                    );
                },
            ]);

        return $projector;
    }
}

<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Model\User\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

/**
 * @method \Prooph\EventStore\Projection\ReadModel readModel()
 */
class UserProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $types = [
            'verified' => 'boolean',
            'active'   => 'boolean',
            'roles'    => 'json',
        ];

        $projector->fromStream('user')
            ->when([
                Event\UserWasAddedByAdmin::class => function (
                    array $state,
                    Event\UserWasAddedByAdmin $event
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'user_id'    => $event->aggregateId(),
                        'email'      => mb_strtolower($event->email()->toString()),
                        'password'   => $event->encodedPassword(),
                        // if sent an invite, then account is not verified
                        // if they didn't send an invite, then account is verified
                        // because there's no way for them to verify the account
                        'verified'   => !$event->sendInvite(),
                        'active'     => $event->active(),
                        'roles'      => [$event->role()->getValue()],
                        'first_name' => $event->firstName()->toString(),
                        'last_name'  => $event->lastName()->toString(),
                    ], $types);
                },

                Event\MinimalUserWasAddedByAdmin::class => function (
                    array $state,
                    Event\MinimalUserWasAddedByAdmin $event
                ) use ($types): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'user_id'    => $event->aggregateId(),
                        'email'      => mb_strtolower($event->email()->toString()),
                        'password'   => $event->encodedPassword(),
                        'verified'   => true,
                        'active'     => true,
                        'roles'      => [$event->role()->getValue()],
                    ], $types);
                },

                Event\UserWasUpdatedByAdmin::class => function (
                    array $state,
                    Event\UserWasUpdatedByAdmin $event
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
                        ],
                        $types,
                    );
                },

                Event\AdminChangedPassword::class => function (
                    array $state,
                    Event\AdminChangedPassword $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'password' => $event->encodedPassword(),
                        ]
                    );
                },

                Event\UserVerifiedByAdmin::class => function (
                    array $state,
                    Event\UserVerifiedByAdmin $event
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
                    Event\UserActivatedByAdmin $event
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
                    Event\UserDeactivatedByAdmin $event
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
                    Event\UserUpdatedProfile $event
                ): void {
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
                        ]
                    );
                },

                Event\ChangedPassword::class => function (
                    array $state,
                    Event\ChangedPassword $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'password' => $event->encodedPassword(),
                        ]
                    );
                },

                Event\PasswordUpgraded::class => function (
                    array $state,
                    Event\PasswordUpgraded $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'password' => $event->encodedPassword(),
                        ]
                    );
                },

                Event\UserVerified::class => function (
                    array $state,
                    Event\UserVerified $event
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

                Event\UserLoggedIn::class => function (
                    array $state,
                    Event\UserLoggedIn $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'loggedIn',
                        $event->userId()->toString(),
                        $event->createdAt()
                    );
                },
            ]);

        return $projector;
    }
}

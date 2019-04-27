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
        $projector->fromStream('user')
            ->when([
                Event\UserWasCreatedByAdmin::class => function (
                    array $state,
                    Event\UserWasCreatedByAdmin $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'user_id'    => $event->aggregateId(),
                        'email'      => $event->email()->toString(),
                        'password'   => $event->encodedPassword(),
                        // if sent an invite, then account is not verified
                        // if they didn't send an invite, then account is verified
                        // because there's no way for them to verify the account
                        'verified'   => !$event->sendInvite(),
                        'active'     => $event->active(),
                        'roles'      => [$event->role()->getValue()],
                        'first_name' => $event->firstName()->toString(),
                        'last_name'  => $event->lastName()->toString(),
                    ], [
                        'roles' => 'json',
                    ]);
                },

                Event\MinimalUserWasCreatedByAdmin::class => function (
                    array $state,
                    Event\MinimalUserWasCreatedByAdmin $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'user_id'    => $event->aggregateId(),
                        'email'      => $event->email()->toString(),
                        'password'   => $event->encodedPassword(),
                        'verified'   => true,
                        'active'     => true,
                        'roles'      => [$event->role()->getValue()],
                    ], [
                        'roles' => 'json',
                    ]);
                },

                Event\AdminUpdatedUser::class => function (
                    array $state,
                    Event\AdminUpdatedUser $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'email'      => $event->email()->toString(),
                            'roles'      => [$event->role()->getValue()],
                            'first_name' => $event->firstName()->toString(),
                            'last_name'  => $event->lastName()->toString(),
                        ],
                        [
                            'roles' => 'json',
                        ]
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
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'verified' => true,
                        ]
                    );
                },

                Event\UserActivatedByAdmin::class => function (
                    array $state,
                    Event\UserActivatedByAdmin $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'active' => true,
                        ]
                    );
                },

                Event\UserDeactivatedByAdmin::class => function (
                    array $state,
                    Event\UserDeactivatedByAdmin $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'active' => false,
                        ]
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
                            'email'      => $event->email()->toString(),
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

                Event\UserVerified::class => function (
                    array $state,
                    Event\UserVerified $event
                ): void {
                    /** @var UserReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->userId()->toString(),
                        [
                            'verified' => true,
                        ]
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

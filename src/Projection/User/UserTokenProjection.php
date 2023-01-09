<?php

declare(strict_types=1);

namespace App\Projection\User;

use App\Model\User\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

/**
 * @method \Prooph\EventStore\Projection\ReadModel readModel()
 */
class UserTokenProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $projector->fromStream('user')
            ->when([
                Event\InviteSent::class           => function (
                    array $state,
                    Event\InviteSent $event,
                ): void {
                    /** @var UserTokenReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'add',
                        [
                            'token'        => $event->token()->toString(),
                            'user_id'      => $event->userId()->toString(),
                            'generated_at' => $event->createdAt(),
                        ],
                        [
                            'generated_at' => 'datetime',
                        ],
                    );
                },
                Event\TokenGenerated::class       => function (
                    array $state,
                    Event\TokenGenerated $event,
                ): void {
                    /** @var UserTokenReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'add',
                        [
                            'token'        => $event->token()->toString(),
                            'user_id'      => $event->userId()->toString(),
                            'generated_at' => $event->createdAt(),
                        ],
                        [
                            'generated_at' => 'datetime',
                        ],
                    );
                },

                Event\PasswordRecoverySent::class => function (
                    array $state,
                    Event\PasswordRecoverySent $event,
                ): void {
                    /** @var UserTokenReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'add',
                        [
                            'token'        => $event->token()->toString(),
                            'user_id'      => $event->userId()->toString(),
                            'generated_at' => $event->createdAt(),
                        ],
                        [
                            'generated_at' => 'datetime',
                        ],
                    );
                },

                Event\UserVerified::class         => function (
                    array $state,
                    Event\UserVerified $event,
                ): void {
                    /** @var UserTokenReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'removeAllForUser',
                        $event->userId()->toString(),
                    );
                },

                Event\ChangedPassword::class      => function (
                    array $state,
                    Event\ChangedPassword $event,
                ): void {
                    /** @var UserTokenReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'removeAllForUser',
                        $event->userId()->toString(),
                    );
                },
            ]);

        return $projector;
    }
}

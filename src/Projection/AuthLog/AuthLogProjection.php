<?php

declare(strict_types=1);

namespace App\Projection\AuthLog;

use App\Model\Auth\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

class AuthLogProjection implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $types = ['occurred_at' => 'datetime_immutable'];

        $projector->fromStream('auth')
            ->when([
                Event\UserLoggedIn::class => function (
                    array $state,
                    Event\UserLoggedIn $event,
                ) use ($types): void {
                    /** @var AuthLogReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'auth_log_id'          => $event->aggregateId(),
                        'event_type'           => 'login',
                        'user_id'              => $event->userId()->toString(),
                        'impersonated_user_id' => null,
                        'email'                => $event->email()->toString(),
                        'ip_address'           => $event->ipAddress(),
                        'user_agent'           => $event->userAgent(),
                        'route'                => $event->route(),
                        'error_message'        => null,
                        'occurred_at'          => $event->createdAt(),
                    ], $types);
                },
                Event\UserFailedToLogin::class => function (
                    array $state,
                    Event\UserFailedToLogin $event,
                ) use ($types): void {
                    /** @var AuthLogReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'auth_log_id'          => $event->aggregateId(),
                        'event_type'           => 'login_failed',
                        'user_id'              => $event->userId()?->toString(),
                        'impersonated_user_id' => null,
                        'email'                => $event->email(),
                        'ip_address'           => $event->ipAddress(),
                        'user_agent'           => $event->userAgent(),
                        'route'                => $event->route(),
                        'error_message'        => $event->exceptionMessage(),
                        'occurred_at'          => $event->createdAt(),
                    ], $types);
                },

                Event\UserStartedImpersonating::class => function (
                    array $state,
                    Event\UserStartedImpersonating $event,
                ) use ($types): void {
                    /** @var AuthLogReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'auth_log_id'          => $event->aggregateId(),
                        'event_type'           => 'impersonation_started',
                        'user_id'              => $event->adminUserId()->toString(),
                        'impersonated_user_id' => $event->impersonatedUserId()->toString(),
                        'email'                => $event->impersonatedEmail()->toString(),
                        'ip_address'           => $event->ipAddress(),
                        'user_agent'           => $event->userAgent(),
                        'route'                => $event->route(),
                        'error_message'        => null,
                        'occurred_at'          => $event->createdAt(),
                    ], $types);
                },
                Event\UserEndedImpersonating::class => function (
                    array $state,
                    Event\UserEndedImpersonating $event,
                ) use ($types): void {
                    /** @var AuthLogReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        'auth_log_id'          => $event->aggregateId(),
                        'event_type'           => 'impersonation_ended',
                        'user_id'              => $event->adminUserId()->toString(),
                        'impersonated_user_id' => $event->impersonatedUserId()->toString(),
                        'email'                => null,
                        'ip_address'           => $event->ipAddress(),
                        'user_agent'           => $event->userAgent(),
                        'route'                => $event->route(),
                        'error_message'        => null,
                        'occurred_at'          => $event->createdAt(),
                    ], $types);
                },
            ]);

        return $projector;
    }
}

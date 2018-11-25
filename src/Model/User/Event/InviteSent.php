<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\EventSourcing\AggregateChanged;
use App\Model\EmailGatewayMessageId;
use App\Model\NotificationGatewayId;
use App\Model\User\UserId;

class InviteSent extends AggregateChanged
{
    public static function now(UserId $userId, NotificationGatewayId $messageId): self
    {
        $event = self::occur($userId->toString(), [
            'messageId' => $messageId->toString(),
        ]);

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function messageId(): NotificationGatewayId
    {
        return EmailGatewayMessageId::fromString($this->payload()['messageId']);
    }
}

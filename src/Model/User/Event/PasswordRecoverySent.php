<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\EventSourcing\AggregateChanged;
use App\Model\EmailGatewayMessageId;
use App\Model\NotificationGatewayId;
use App\Model\User\Token;
use App\Model\User\UserId;

class PasswordRecoverySent extends AggregateChanged
{
    public static function now(
        UserId $userId,
        Token $token,
        NotificationGatewayId $messageId
    ): self {
        $event = self::occur($userId->toString(), [
            'token'     => $token->toString(),
            'messageId' => $messageId->toString(),
        ]);

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function token(): Token
    {
        return Token::fromString($this->payload()['token']);
    }

    public function messageId(): NotificationGatewayId
    {
        return EmailGatewayMessageId::fromString($this->payload()['messageId']);
    }
}

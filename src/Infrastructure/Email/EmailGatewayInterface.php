<?php

declare(strict_types=1);

namespace App\Infrastructure\Email;

use App\Model\Email;
use App\Model\EmailGatewayMessageId;

interface EmailGatewayInterface
{
    public function send(
        $templateId,
        Email $to,
        array $templateData
    ): EmailGatewayMessageId;
}

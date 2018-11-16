<?php

declare(strict_types=1);

namespace App\Model\Demo\Handler;

use App\Model\Demo\Command\DoDemo;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DoDemoHandler implements MessageHandlerInterface
{
    public function __invoke(DoDemo $command): void
    {
        dump($command);
    }
}

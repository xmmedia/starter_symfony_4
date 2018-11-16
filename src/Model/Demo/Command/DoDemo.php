<?php

declare(strict_types=1);

namespace App\Model\Demo\Command;

// @todo reorganize
// @todo pull in tests
use App\Commanding\Command;

final class DoDemo extends Command
{
    public static function now(string $message): self
    {
        return new self([
            'message' => $message,
        ]);
    }
}

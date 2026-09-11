<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\HttpFoundation\Response;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// maintenance mode is checked before the kernel's created, so it works even if the app won't boot.
// See "Maintenance mode" in AGENTS.md
return static fn (array $context): Kernel|Response => MaintenanceGate::handleGlobals(dirname(__DIR__).'/var/maintenance')
    ?? new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

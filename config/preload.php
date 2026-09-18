<?php

declare(strict_types=1);

foreach (glob(dirname(__DIR__).'/cache/prod/*.preload.php') ?: [] as $file) {
    require $file;
}

<?php

declare(strict_types=1);

namespace App\Model\Page;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\UuidInterface;
use Xm\SymfonyBundle\Model\ValueObject;

class PageId implements ValueObject, UuidInterface
{
    use UuidId;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Xm\SymfonyBundle\Util\Json;

class JsonResolver implements ResolverInterface
{
    public function __invoke($content): string
    {
        return Json::encode($content);
    }
}

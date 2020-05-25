<?php

declare(strict_types=1);

namespace App\Util;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Slugger
{
    public static function path(string $path): string
    {
        $pathParts = explode('/', trim($path, '/'));
        $slugger = new AsciiSlugger();

        $parts = array_map(function (string $part) use ($slugger): string {
            $result = $slugger->slug($part);

            Assert::notEmpty($result, 'The path part cannot be empty. Original "%s"');

            return $result->toString();
        }, $pathParts);

        return '/'.implode('/', $parts);
    }
}

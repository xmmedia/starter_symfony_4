<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Error;

use App\GraphQl\Error\PasswordNotAllowedError;
use App\Tests\BaseTestCase;

/**
 * The frontend matches the code (GraphQlErrorCodes in common/lib.js), so it mustn't change.
 */
final class PasswordNotAllowedErrorTest extends BaseTestCase
{
    public function testCode(): void
    {
        $message = $this->faker()->sentence();

        $error = new PasswordNotAllowedError($message);

        $this->assertSame(['code' => 'PASSWORD_NOT_ALLOWED'], $error->getExtensions());
        $this->assertSame($message, $error->getMessage());
    }
}

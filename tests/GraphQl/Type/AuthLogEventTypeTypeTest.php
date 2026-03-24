<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Type;

use App\GraphQl\Type\AuthLogEventTypeType;
use App\Model\AuthLog\AuthLogEventType;
use App\Tests\BaseTestCase;

class AuthLogEventTypeTypeTest extends BaseTestCase
{
    public function testAliases(): void
    {
        $result = AuthLogEventTypeType::getAliases();

        $this->assertEquals(['AuthLogEventType'], $result);
    }

    public function testValues(): void
    {
        $type = new AuthLogEventTypeType();
        $values = $type->getValues();
        $valueNames = array_map(static fn ($v): string => $v->name, $values);

        $this->assertContains('LOGIN', $valueNames);
        $this->assertContains('LOGIN_FAILED', $valueNames);
        $this->assertContains('IMPERSONATION_STARTED', $valueNames);
        $this->assertContains('IMPERSONATION_ENDED', $valueNames);
    }

    public function testValuesMappedFromEnum(): void
    {
        $type = new AuthLogEventTypeType();
        $values = $type->getValues();
        $valueMap = [];

        foreach ($values as $v) {
            $valueMap[$v->name] = $v->value;
        }

        $this->assertSame(AuthLogEventType::LOGIN, $valueMap['LOGIN']);
        $this->assertSame(AuthLogEventType::LOGIN_FAILED, $valueMap['LOGIN_FAILED']);
        $this->assertSame(AuthLogEventType::IMPERSONATION_STARTED, $valueMap['IMPERSONATION_STARTED']);
        $this->assertSame(AuthLogEventType::IMPERSONATION_ENDED, $valueMap['IMPERSONATION_ENDED']);
    }
}

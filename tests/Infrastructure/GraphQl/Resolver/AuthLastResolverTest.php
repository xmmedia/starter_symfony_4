<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver;

use App\Infrastructure\GraphQl\Resolver\AuthLastResolver;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

class AuthLastResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $authException = Mockery::mock(AuthenticationException::class);
        $authException->shouldReceive('getMessageKey')
            ->once()
            ->andReturn('key');
        $authException->shouldReceive('getMessageData')
            ->once()
            ->andReturn([]);

        $authUtils = Mockery::mock(AuthenticationUtils::class);
        $authUtils->shouldReceive('getLastAuthenticationError')
            ->once()
            ->andReturn($authException);
        $authUtils->shouldReceive('getLastUsername')
            ->once()
            ->andReturn('email@email.com');

        $translator = Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')
            ->once()
            ->with('key', [], 'security')
            ->andReturn('message');

        $resolver = new AuthLastResolver($authUtils, $translator);

        $result = $resolver->get();

        $expected = [
            'email' => 'email@email.com',
            'error' => 'message',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testNoExceptionNoUsername(): void
    {
        $authUtils = Mockery::mock(AuthenticationUtils::class);
        $authUtils->shouldReceive('getLastAuthenticationError')
            ->once()
            ->andReturnNull();
        $authUtils->shouldReceive('getLastUsername')
            ->once()
            ->andReturnNull();

        $translator = Mockery::mock(TranslatorInterface::class);

        $resolver = new AuthLastResolver($authUtils, $translator);

        $result = $resolver->get();

        $expected = [
            'email' => null,
            'error' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testNoException(): void
    {
        $authUtils = Mockery::mock(AuthenticationUtils::class);
        $authUtils->shouldReceive('getLastAuthenticationError')
            ->once()
            ->andReturnNull();
        $authUtils->shouldReceive('getLastUsername')
            ->once()
            ->andReturn('email@email.com');

        $translator = Mockery::mock(TranslatorInterface::class);

        $resolver = new AuthLastResolver($authUtils, $translator);

        $result = $resolver->get();

        $expected = [
            'email' => 'email@email.com',
            'error' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testAliases(): void
    {
        $result = AuthLastResolver::getAliases();

        $expected = [
            'get' => 'app.graphql.resolver.auth_last',
        ];

        $this->assertEquals($expected, $result);

        $resolver = new AuthLastResolver(
            Mockery::mock(AuthenticationUtils::class),
            Mockery::mock(TranslatorInterface::class)
        );

        $this->assertHasAllResolverMethods($resolver);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\UserProvider;
use App\Tests\UsesFaker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * form_login is throttled by security.firewalls.main.login_throttling.
 */
class LoginThrottlingTest extends WebTestCase
{
    use UsesFaker;

    private const int MAX_ATTEMPTS = 10;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        // keep the stubbed services between requests
        $this->client->disableReboot();

        $userProvider = $this->createStub(UserProvider::class);
        $userProvider->method('loadUserByIdentifier')->willThrowException(new UserNotFoundException());
        self::getContainer()->set(UserProvider::class, $userProvider);

        // LoginLoggerSubscriber logs each failure
        $commandBus = $this->createStub(MessageBusInterface::class);
        $commandBus->method('dispatch')->willReturnCallback(
            static fn (object $message): Envelope => new Envelope($message),
        );
        self::getContainer()->set('messenger.bus.commands', $commandBus);

        // the limiter state is cached, so it'd otherwise carry over between runs
        self::getContainer()->get('cache.rate_limiter')->clear();
    }

    public function testThrottledAfterMaxFailedAttempts(): void
    {
        $email = $this->faker()->email();

        for ($i = 0; $i < self::MAX_ATTEMPTS; ++$i) {
            $this->assertNotInstanceOf(TooManyLoginAttemptsAuthenticationException::class, $this->login($email));
        }

        $error = $this->login($email);

        $this->assertInstanceOf(TooManyLoginAttemptsAuthenticationException::class, $error);
        $this->assertSame(
            'Too many failed login attempts, please try again in 15 minutes.',
            self::getContainer()->get(TranslatorInterface::class)->trans(
                $error->getMessageKey(),
                $error->getMessageData(),
                'security',
            ),
        );
    }

    public function testOtherEmailNotThrottled(): void
    {
        $email = $this->faker()->email();

        for ($i = 0; $i <= self::MAX_ATTEMPTS; ++$i) {
            $this->login($email);
        }

        $this->assertNotInstanceOf(
            TooManyLoginAttemptsAuthenticationException::class,
            $this->login($this->faker()->unique()->email()),
        );
    }

    /**
     * @phpstan-impure
     */
    private function login(string $email): ?AuthenticationException
    {
        $this->client->request(
            Request::METHOD_POST,
            '/login',
            [
                '_username'   => $email,
                '_password'   => $this->faker()->password(),
                // stateless: validated on the origin, see framework.csrf_protection
                '_csrf_token' => self::getContainer()->get(CsrfTokenManagerInterface::class)
                    ->getToken('authenticate')
                    ->getValue(),
            ],
            [],
            ['HTTP_ORIGIN' => 'http://localhost'],
        );

        $this->assertResponseRedirects();

        return $this->client->getRequest()
            ->getSession()
            ->get(SecurityRequestAttributes::AUTHENTICATION_ERROR);
    }
}

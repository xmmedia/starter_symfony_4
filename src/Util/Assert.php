<?php

declare(strict_types=1);

namespace App\Util;

use App\Model\User\User;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class Assert extends \Webmozart\Assert\Assert
{
    public static function passwordLength(string $password): void
    {
        self::lengthBetween(
            $password,
            User::PASSWORD_MIN_LENGTH,
            BasePasswordEncoder::MAX_PASSWORD_LENGTH,
            'The password must length must be between %2$d and %3$d. Got '.\strlen($password)
        );
    }

    public static function compromisedPassword(string $password, HttpClient $httpClient = null): void
    {
        $endpoint = 'https://api.pwnedpasswords.com/range/%s';
        if (null === $httpClient) {
            $httpClient = HttpClient::create();
        }

        $hash = strtoupper(sha1($password));
        $hashPrefix = substr($hash, 0, 5);
        $url = sprintf($endpoint, $hashPrefix);

        try {
            $result = $httpClient->request('GET', $url)->getContent();
        } catch (ExceptionInterface $e) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unable to check for compromised password. HTTP error: %s',
                    $e->getMessage()
                ), 0, $e
            );
        }

        foreach (explode("\r\n", $result) as $line) {
            list($hashSuffix, $count) = explode(':', $line);

            // reject if in more than 3 breaches
            if ($hashPrefix.$hashSuffix === $hash && 3 <= (int) $count) {
                throw new \InvalidArgumentException(
                    'The entered password has been compromised.'
                );
            }
        }
    }
}

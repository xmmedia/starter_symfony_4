<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Credentials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    // @todo-symfony
    public const DEFAULT_REDIRECT = '/admin';

    /** @var RouterInterface */
    private $router;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(
        RouterInterface $router,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request): bool
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request): Credentials
    {
        $credentials = Credentials::build(
            $request->request->get('email'),
            $request->request->get('password')
        );

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials->email()
        );

        return $credentials;
    }

    /**
     * @param Credentials $credentials
     *
     * @return \App\Entity\User
     */
    public function getUser(
        $credentials,
        UserProviderInterface $userProvider
    ): UserInterface {
        // CSRF check is done in CsrfValidationSubscriber

        // Load / create our user however you need.
        // You can do this by calling the user provider, or with custom logic here.
        try {
            $user = $userProvider->loadUserByIdentifier($credentials->email());
        } catch (UserNotFoundException $e) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Account could not be found.', [], 0, $e);
        }

        return $user;
    }

    /**
     * @param Credentials $credentials
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid(
            $user,
            (string) $credentials->password(),
        );
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        if (!$credentials instanceof Credentials) {
            return null;
        }

        return $credentials->password();
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ): ?Response {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $providerKey
        )) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse(self::DEFAULT_REDIRECT);
    }

    protected function getLoginUrl(): string
    {
        return $this->router->generate('app_login');
    }
}

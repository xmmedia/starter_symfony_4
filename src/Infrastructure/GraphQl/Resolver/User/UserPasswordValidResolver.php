<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver\User;

use App\Projection\User\UserFinder;
use App\Security\Security;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\StringUtil;

class UserPasswordValidResolver implements ResolverInterface
{
    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var Security */
    private $security;

    public function __construct(
        UserPasswordEncoderInterface $userPasswordEncoder,
        Security $security
    ) {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->security = $security;
    }

    public function __invoke(string $password): array
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            throw new \RuntimeException('Must be logged in to access.');
        }

        return [
            'valid' => $this->userPasswordEncoder->isPasswordValid(
                $currentUser,
                $password
            ),
        ];
    }
}

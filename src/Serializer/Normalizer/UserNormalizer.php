<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\User;
use App\Serializer\SerializerGroupAwareTrait;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use SerializerGroupAwareTrait;

    /**
     * @param User $user
     */
    public function normalize(
        $user,
        $format = null,
        array $context = array()
    ): array {
        if (!$user instanceof User) {
            throw new InvalidArgumentException(
                sprintf('The object must be an instance of "%s".', User::class)
            );
        }

        $this->context = $context;

        $data = [];

        if ($this->hasGroup(['user_admin'])) {
            $data['id'] = $user->id()->toString();
            $data['email'] = $user->email()->toString();
            $data['name'] = $user->name();
            $data['firstName'] = $user->firstName() ? $user->firstName()->toString() : null;
            $data['lastName'] = $user->lastName() ? $user->lastName()->toString() : null;
            $data['verified'] = $user->verified();
            $data['active'] = $user->active();
            $data['roles'] = $user->roles();
            if (null !== $user->lastLogin()) {
                $data['lastLogin'] = $this->normalizer->normalize(
                    $user->lastLogin(),
                    $format,
                    $context
                );
            } else {
                $data['lastLogin'] = null;
            }
            $data['loginCount'] = $user->loginCount();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof User;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\RequestCsrfCheck;
use App\Exception\InvalidForm;
use App\Form\UserProfileType;
use App\Model\User\Command\UpdateUserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
 * @todo test if this & below are both needed
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class UserProfileController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/user/profile/save",
     *     name="api_user_profile",
     *     methods={"POST"},
     *     defaults={"_format": "json"}
     * )
     *
     * @param UserInterface|\App\Entity\User $user
     */
    public function save(
        Request $request,
        MessageBusInterface $commandBus,
        UserInterface $user = null,
        FormFactoryInterface $formFactory
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        // @todo check what else FOS User does

        $form = $formFactory
            ->create(UserProfileType::class)
            ->submit($request->request->get('user'));

        if (!$form->isValid()) {
            throw InvalidForm::fromForm($form);
        }

        $commandBus->dispatch(UpdateUserProfile::withData(
            $user->id(),
            $form->getData()['email'],
            $form->getData()['firstName'],
            $form->getData()['firstName']
        ));

        return $this->json(['success' => true]);
    }
}

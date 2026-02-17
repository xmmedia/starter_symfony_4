<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Service\DefaultRouteProvider;
use App\Security\Security;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @codeCoverageIgnore
 */
#[IsGranted('ROLE_USER')]
final class SwitchUserRedirectController extends AbstractController
{
    #[Route('/switch-user/redirect', name: 'app_switch_user_redirect')]
    public function __invoke(Request $request, Security $security, DefaultRouteProvider $defaultRoute): Response
    {
        if ($security->isGranted('ROLE_SUPER_ADMIN')) {
            $userId = $request->query->get('userId');
            if (null !== $userId && Uuid::isValid($userId)) {
                return $this->redirectToRoute('admin_default', ['path' => '/user/'.$userId.'/view']);
            }
        }

        return $this->redirectToRoute(...$defaultRoute());
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\InvalidForm;
use App\Form\EnquiryType;
use App\Model\Enquiry\Command\SubmitEnquiryForm;
use App\Model\Enquiry\EnquiryId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class EnquiryController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/enquiry/submit",
     *     name="enquiry_submit",
     *     methods={"POST"},
     *     defaults={"_format": "json"}
     * )
     */
    public function createOrder(
        Request $request,
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory
    ): JsonResponse {
        $this->checkCsrf($request, 'enquiry');

        $enquiry = $request->request->get('enquiry');

        $form = $formFactory
            ->create(EnquiryType::class)
            ->submit($enquiry);

        if (!$form->isValid()) {
            throw InvalidForm::fromForm($form);
        }

        $commandBus->dispatch(SubmitEnquiryForm::withData(
            EnquiryId::generate(),
            $form->getData()['name'],
            $form->getData()['email'],
            $form->getData()['message']
        ));

        return $this->json(['success' => true]);
    }
}

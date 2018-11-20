<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Form\FormInterface;

final class InvalidForm extends \InvalidArgumentException
{
    /** @var FormInterface */
    private $form;

    public static function fromForm(FormInterface $form): self
    {
        return new self($form);
    }

    private function __construct(FormInterface $form)
    {
        $this->form = $form;

        parent::__construct();
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }
}

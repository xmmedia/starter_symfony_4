<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Form\FormInterface;

final class FormValidationException extends \InvalidArgumentException
{
    /** @var FormInterface */
    private $form;

    private $field;

    public static function fromForm(FormInterface $form, ?string $field = null): self
    {
        return new self($form, $field);
    }

    private function __construct(FormInterface $form, ?string $field = null)
    {
        $this->form = $form;
        $this->field = $field;

        parent::__construct();
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getField(): ?string
    {
        return $this->field;
    }
}

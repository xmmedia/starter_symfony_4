<?php

declare(strict_types=1);

namespace App\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

class TypeTestCase extends \Symfony\Component\Form\Test\TypeTestCase
{
    use ValidatorExtensionTrait;
    use MockeryPHPUnitIntegration;
    use UsesFaker;

    /**
     * @var Container for use when validators have constructor arguments
     */
    protected $validatorContainer;

    protected function getExtensions(): array
    {
        $extensions = parent::getExtensions();

        $this->validatorContainer = new Container();

        $validator = (Validation::createValidatorBuilder())
            ->setConstraintValidatorFactory(
                new ContainerConstraintValidatorFactory($this->validatorContainer)
            )
            ->getValidator();

        $extensions[] = new ValidatorExtension($validator);

        return $extensions;
    }

    protected function assertFormIsValid(FormInterface $form): void
    {
        $this->assertTrue($form->isSynchronized(), 'The form data is not synchronized.');
        $this->assertTrue(
            $form->isValid(),
            'The following fields are invalid: '.implode(', ', $this->getFormErrors($form))
        );
    }

    protected function hasAllFormFields(FormInterface $form, array $formData): void
    {
        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getFormErrors(FormInterface $form): array
    {
        // returns a structured array, the rest will flatten
        $errors = $this->invalidFields($form);

        $strings = [];

        if ($errors['root'] ?? false) {
            $strings[] = sprintf('ROOT [%s]', implode(', ', $errors['root']));
        }

        foreach ($errors['children'] as $field => $childErrors) {
            if ($childErrors) {
                $all = [];

                array_walk_recursive(
                    $childErrors,
                    function ($a) use (&$all) {
                        $all[] = $a;
                    }
                );

                if ($all) {
                    $strings[] = sprintf(
                        '%s [%s]',
                        $field,
                        implode(', ', $all)
                    );
                }
            }
        }

        return $strings;
    }

    private function invalidFields(FormInterface $data)
    {
        $form = $errors = [];

        foreach ($data->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        if ($errors) {
            if ($data->isRoot()) {
                $form['root'] = $errors;
            } else {
                $form = $errors;
            }
        }

        $children = [];
        foreach ($data->all() as $child) {
            if ($child instanceof FormInterface) {
                $children[$child->getName()] = $this->invalidFields($child);
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        return $form;
    }
}

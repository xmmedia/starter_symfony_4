<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\Page\Content;
use App\Model\Page\Service\PageContentValidator;
use App\Util\Assert;

class CmsPageContentValidator implements PageContentValidator
{
    /** @var array */
    private $templates;

    /** @var string */
    private $defaultTemplate;

    public function __construct(array $templates)
    {
        $this->templates = $templates;

        foreach ($this->templates as $templateName => $template) {
            if ($template['default']) {
                $this->defaultTemplate = $templateName;
            }
        }
    }

    public function __invoke(Content $content): void
    {
        $data = $content->toArray();

        $template = $content->template() ?: $this->defaultTemplate;
        if (!\array_key_exists($template, $this->templates)) {
            throw new \InvalidArgumentException(sprintf('The config for template "%s" doesn\'t exist.', $template));
        }

        foreach ($this->templates[$template]['items'] as $itemKey => $item) {
            try {
                if ($item['required'] ?? false) {
                    Assert::keyExists($data, $itemKey);
                    Assert::notEmpty($data[$itemKey]);
                }

                if ($item['min'] ?? false) {
                    Assert::minLength($data[$itemKey], $item['min']);
                }
                if ($item['max'] ?? false) {
                    Assert::maxLength($data[$itemKey], $item['max']);
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf('The content item "%s" is invalid: %s', $itemKey, $e->getMessage()));
            }
        }
    }
}

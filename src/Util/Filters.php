<?php

declare(strict_types=1);

namespace App\Util;

use Webmozart\Assert\Assert;

abstract class Filters
{
    /** @var array */
    protected $filters;

    public static function fromArray(?array $filters): self
    {
        return new static($filters ?? []);
    }

    protected function __construct(array $filters)
    {
        $availableFields = $this->getFields();
        if (empty($availableFields)) {
            Assert::notEmpty(
                $availableFields,
                'The filter class must have at least 1 filter constant.'
            );
        }

        $filters = array_map([StringUtil::class, 'trim'], $filters);

        foreach ($filters as $key => $value) {
            Assert::oneOf(
                $key,
                $availableFields,
                '%s is not an available filter.'
            );
        }

        $filters = $this->parseFilters($filters);

        $filters = array_filter($filters, [$this, 'notEmpty']);

        $this->filters = $filters;
    }

    public function applied(string $field): bool
    {
        return \array_key_exists($field, $this->filters);
    }

    /**
     * @return mixed|null
     */
    public function get(string $field)
    {
        return $this->filters[$field] ?? null;
    }

    public function toArray(): array
    {
        return $this->filters;
    }

    protected function parseFilters(array $filters): array
    {
        return $filters;
    }

    protected function notEmpty($value): bool
    {
        return !(empty($value) && !\is_bool($value) && !\is_int($value) && '0' !== $value);
    }

    protected function isTrue($value): bool
    {
        return true === $value || 'true' === $value;
    }

    private function getFields(): array
    {
        $reflection = new \ReflectionClass(static::class);
        $constants = [];

        foreach ($reflection->getReflectionConstants() as $reflConstant) {
            if ($reflConstant->isPublic()) {
                $constants[$reflConstant->getName()] = $reflConstant->getValue();
            }
        }

        return $constants;
    }
}

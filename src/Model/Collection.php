<?php

declare(strict_types=1);

namespace App\Model;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use ReflectionClass;
use RuntimeException;
use SplFixedArray;
use Traversable;

/**
 * Based heavily on: https://github.com/jkoudys/immutable.php/blob/master/src/Collection/ImmArray.php.
 */
abstract class Collection implements Iterator, ArrayAccess, Countable, JsonSerializable
{
    /** @var Traversable|SplFixedArray */
    protected $items;

    // @todo add some way of check that all items are of correct type

    public static function fromArray(array $items)
    {
        return new static(SplFixedArray::fromArray($items));
    }

    public static function fromItems(Traversable $arr, callable $cb = null): self
    {
        // We can only do it this way if we can count it
        if ($arr instanceof Countable) {
            $items = new SplFixedArray(count($arr));
            foreach ($arr as $i => $el) {
                // Apply a mapping function if available
                if ($cb) {
                    $items[$i] = $cb($el, $i);
                } else {
                    $items[$i] = $el;
                }
            }

            return new static($items);
        }

        // If we can't count it, it's simplest to iterate into an array first
        $asArray = iterator_to_array($arr);
        if ($cb) {
            return static::fromArray(array_map($cb, $asArray, array_keys($asArray)));
        }

        return static::fromArray($asArray);
    }

    private function __construct(Traversable $items)
    {
        $this->items = $items;
    }

    public function toArray(): array
    {
        return $this->items->toArray();
    }

    public function map(callable $cb): self
    {
        $count = count($this);
        $items = new SplFixedArray($count);

        for ($i = 0; $i < $count; ++$i) {
            $items[$i] = $cb($this->items[$i], $i, $this);
        }

        return new static($items);
    }

    public function walk(callable $cb): self
    {
        foreach ($this as $i => $el) {
            $cb($el, $i, $this);
        }

        return $this;
    }

    public function find(callable $cb)
    {
        foreach ($this->items as $i => $el) {
            if ($cb($el, $i, $this)) {
                return $el;
            }
        }

        return;
    }

    public function reduce(callable $cb, $initial = null)
    {
        foreach ($this->items as $i => $el) {
            $initial = $cb($initial, $el, $i, $this);
        }

        return $initial;
    }

    public function concat(): self
    {
        $args = func_get_args();
        array_unshift($args, $this->items);

        // Concat this iterator, and variadic args
        $class = new ReflectionClass(ConcatIterator::class);
        $concatIt = $class->newInstanceArgs($args);

        // Create as new immutable's iterator
        return new static($concatIt);
    }

    public function diff(self $other, callable $diffCb = null)
    {
        if (null === $diffCb) {
            $diffCb = function ($a, $b) {
                if ($a < $b) {
                    return -1;
                } elseif ($a > $b) {
                    return 1;
                } else {
                    return 0;
                }
            };
        }

        $diff1 = array_udiff($this->toArray(), $other->toArray(), $diffCb);
        $diff2 = array_udiff($other->toArray(), $this->toArray(), $diffCb);

        return self::fromArray(array_values($diff1 + $diff2));
    }

    public function sameAs(self $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        if (count($this->items) !== count($other->items)) {
            return false;
        }

        return 0 === $this->diff($other, null)->count();
    }

    public function count()
    {
        return count($this->items);
    }

    public function current()
    {
        return $this->items->current();
    }

    public function key()
    {
        return $this->items->key();
    }

    public function next()
    {
        return $this->items->next();
    }

    public function rewind()
    {
        return $this->items->rewind();
    }

    public function valid()
    {
        return $this->items->valid();
    }

    public function offsetExists($offset)
    {
        return $this->items->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->items->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Attempt to mutate immutable '.__CLASS__.' object.');
    }

    public function offsetUnset($offset)
    {
        throw new RuntimeException('Attempt to mutate immutable '.__CLASS__.' object.');
    }

    public function jsonSerialize()
    {
        return $this->items->toArray();
    }
}

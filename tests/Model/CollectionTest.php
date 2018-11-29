<?php

declare(strict_types=1);

namespace App\Tests\Model;

use ArrayIterator;
use Countable;
use Iterator;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testFromItemsMap(): void
    {
        $numberSet = Collection::fromItems(
            new ArrayIterator([1, 2, 3]),
            function ($el) {
                return $el * 2;
            }
        );
        $this->assertSame([2, 4, 6], $numberSet->toArray());
    }

    public function testToArray(): void
    {
        $array = [1, 2, 3, 4, 5];

        $collection = Collection::fromArray($array);

        $this->assertEquals($array, $collection->toArray());
    }

    public function testMap(): void
    {
        $base = [1, 2, 3, 4];
        $doubled = [2, 4, 6, 8];

        $numberSet = Collection::fromArray($base);
        $mapped = $numberSet->map(function ($num) {
            return $num * 2;
        });

        foreach ($mapped as $i => $v) {
            $this->assertEquals($v, $doubled[$i]);
        }
    }

    public function testReduce(): void
    {
        $arIt = new ArrayIterator([1, 2, 3, 4, 5]);
        $numberSet = Collection::fromItems($arIt);

        // Reduce with sum
        $sum = $numberSet->reduce(function ($last, $cur) {
            return $last + $cur;
        }, 0);
        $this->assertEquals(15, $sum);

        // Reduce with string concat
        $concatted = $numberSet->reduce(function ($last, $cur, $i) {
            return $last.'{"'.$i.'":"'.$cur.'"},';
        }, '');
        $this->assertEquals('{"0":"1"},{"1":"2"},{"2":"3"},{"3":"4"},{"4":"5"},', $concatted);
    }

    public function testConcat(): void
    {
        $setA = Collection::fromArray([1, 2, 3]);

        $setB = Collection::fromItems(new ArrayIterator([4, 5, 6]));

        $concatted = $setA->concat($setB);

        $this->assertSame([1, 2, 3, 4, 5, 6], $concatted->toArray());
    }

    public function testConcatArray(): void
    {
        $setA = Collection::fromArray([1, 2, 3]);
        $setB = Collection::fromArray([4, 5, 6]);

        $concatted = $setA->concat($setB);

        $this->assertSame([1, 2, 3, 4, 5, 6], $concatted->toArray());
    }

    public function testDiff(): void
    {
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [1, 4, 5];
        $diff = [2, 3];

        $collection1 = Collection::fromArray($arr1);
        $collection2 = Collection::fromArray($arr2);

        $diffCollection = $collection1->diff($collection2);

        $this->assertInstanceOf(Collection::class, $diffCollection);
        $this->assertEquals($diff, $diffCollection->toArray());
    }

    public function testSameValuesAs(): void
    {
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [1, 2, 3, 4, 5];

        $collection1 = Collection::fromArray($arr1);
        $collection2 = Collection::fromArray($arr2);

        $this->assertTrue($collection1->sameValuesAs($collection2));
    }

    public function testSameValuesAsDiffClass(): void
    {
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [1, 2, 3, 4, 5];

        $collection1 = Collection::fromArray($arr1);
        $collection2 = CollectionOther::fromArray($arr2);

        $this->assertFalse($collection1->sameValuesAs($collection2));
    }

    public function testSameValuesAsDiffItemCount(): void
    {
        $arr1 = [1, 2];
        $arr2 = [1, 2, 3, 4, 5];

        $collection1 = Collection::fromArray($arr1);
        $collection2 = Collection::fromArray($arr2);

        $this->assertFalse($collection1->sameValuesAs($collection2));
    }

    public function testWalk(): void
    {
        $arr = [1, 2, 3];

        $collection = Collection::fromArray($arr);

        $res = $collection->walk(function ($i) use (&$other) {
            $other[] = $i * 2;
        });

        $this->assertEquals($collection, $res);
        $this->assertEquals([2, 4, 6], $other);
    }

    public function testFind(): void
    {
        $arr = [1, 2, 3];

        $collection = Collection::fromArray($arr);

        $res = $collection->find(function ($i) {
            return 1 === $i;
        });

        $this->assertEquals(1, $res);
    }

    public function testFindNone(): void
    {
        $arr = [1];

        $collection = Collection::fromArray($arr);

        $res = $collection->find(function ($i) {
            return 0 === $i;
        });

        $this->assertNull($res);
    }

    public function testOffsetExists(): void
    {
        $collection = Collection::fromArray([1]);

        $this->assertTrue($collection->offsetExists(0));
    }

    public function testOffsetDoesntExist(): void
    {
        $collection = Collection::fromArray([1]);

        $this->assertFalse($collection->offsetExists(1));
    }

    public function testOffsetGet(): void
    {
        $collection = Collection::fromArray([1]);

        $this->assertEquals(1, $collection->offsetGet(0));
    }

    public function testOffsetGetDoesntExist(): void
    {
        $collection = Collection::fromArray([1]);

        $this->expectException(\RuntimeException::class);

        $collection->offsetGet(1);
    }

    public function testOffsetSet(): void
    {
        $collection = Collection::fromArray([1]);

        $this->expectException(\RuntimeException::class);

        $collection->offsetSet(1, 1);
    }

    public function testOffsetUnset(): void
    {
        $collection = Collection::fromArray([1]);

        $this->expectException(\RuntimeException::class);

        $collection->offsetUnset(1);
    }

    public function testJsonSerialize(): void
    {
        $str = json_encode([1]);

        $collection = Collection::fromArray([1]);

        $this->assertEquals($str, json_encode($collection));
    }

    public function testLoadBigSet()
    {
        // Big
        $bigSet = Collection::fromItems(new MD5Iterator(200000));

        $this->assertCount(200000, $bigSet);
    }
}

class Collection extends \App\Model\Collection
{
}
class CollectionOther extends \App\Model\Collection
{
}

// A basic iterator for testing loading large sets
class MD5Iterator implements Iterator, Countable
{
    protected $count;
    protected $position = 0;

    public function __construct($count = 0)
    {
        $this->count = $count;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return md5((string) $this->position);
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return $this->position < $this->count;
    }

    public function count()
    {
        return $this->count;
    }
}

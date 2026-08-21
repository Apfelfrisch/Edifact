<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Segment\Elements;
use Apfelfrisch\Edifact\Test\TestCase;

final class ElementsTest extends TestCase
{
    public function test_adding_and_getting_values(): void
    {
        $elements = new Elements()
            ->addValue('SEG', 'SEG', 'SEG')
            ->addValue('C1', '1', 'foo')
            ->addValue('C1', '2', null);

        static::assertSame('SEG', $elements->getValue('SEG', 'SEG'));
        static::assertSame('foo', $elements->getValue('C1', '1'));
        static::assertNull($elements->getValue('C1', '2'));
        static::assertNull($elements->getValue('C1', 'missing'));
        static::assertNull($elements->getValue('missing', 'missing'));
    }

    public function test_getting_values_by_position(): void
    {
        $elements = new Elements()
            ->addValue('SEG', 'SEG', 'SEG')
            ->addValue('C1', '1', 'foo')
            ->addValue('C1', '2', 'bar');

        static::assertSame('SEG', $elements->getValueFromPosition(0, 0));
        static::assertSame('foo', $elements->getValueFromPosition(1, 0));
        static::assertSame('bar', $elements->getValueFromPosition(1, 1));
        static::assertNull($elements->getValueFromPosition(1, 2));
        static::assertNull($elements->getValueFromPosition(2, 0));
    }

    public function test_getting_the_name_from_the_first_value(): void
    {
        $elements = new Elements()->addValue('UNH', 'UNH', 'UNH');

        static::assertSame('UNH', $elements->getName());
        // The name is memoized
        static::assertSame('UNH', $elements->getName());
    }

    public function test_getting_the_name_of_empty_elements(): void
    {
        static::assertSame('', new Elements()->getName());
    }

    public function test_getting_an_element(): void
    {
        $elements = new Elements()
            ->addValue('C1', '1', 'foo')
            ->addValue('C1', '2', 'bar');

        static::assertSame(['1' => 'foo', '2' => 'bar'], $elements->getElement('C1'));
        static::assertSame([], $elements->getElement('missing'));
    }

    public function test_getting_element_and_component_keys_as_strings(): void
    {
        $elements = new Elements()
            ->addValue('SEG', 'SEG', 'SEG')
            ->addValue('1', '1', 'foo')
            ->addValue('1', '2', 'bar');

        // Numeric string keys are cast to int by PHP; the getters restore strings
        static::assertSame(['SEG', '1'], $elements->getElementKeys());
        static::assertSame(['1', '2'], $elements->getComponentKeys('1'));
        static::assertSame([], $elements->getComponentKeys('missing'));
    }

    public function test_getting_keys_from_positions(): void
    {
        $elements = new Elements()
            ->addValue('SEG', 'SEG', 'SEG')
            ->addValue('C1', '1', 'foo')
            ->addValue('C1', '2', 'bar');

        static::assertSame('SEG', $elements->getElementKeyFromPosition(0));
        static::assertSame('C1', $elements->getElementKeyFromPosition(1));
        static::assertNull($elements->getElementKeyFromPosition(2));

        static::assertSame('1', $elements->getComponentKeyFromPosition('C1', 0));
        static::assertSame('2', $elements->getComponentKeyFromPosition('C1', 1));
        static::assertNull($elements->getComponentKeyFromPosition('C1', 2));
        static::assertNull($elements->getComponentKeyFromPosition('missing', 0));
    }

    public function test_array_casting(): void
    {
        $elements = new Elements()
            ->addValue('SEG', 'SEG', 'SEG')
            ->addValue('C1', '1', 'foo')
            ->addValue('C1', '2', null);

        static::assertSame(
            [
                'SEG' => ['SEG' => 'SEG'],
                'C1' => ['1' => 'foo', '2' => null],
            ],
            $elements->toArray(),
        );
    }
}

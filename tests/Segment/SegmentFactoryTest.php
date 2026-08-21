<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\ConcreteSegmentFactory;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\Fixtures\Unh;
use Apfelfrisch\Edifact\Test\TestCase;
use stdClass;

final class SegmentFactoryTest extends TestCase
{
    public function test_instanciate_the_segment_from_segment_string(): void
    {
        $segFactory = new ConcreteSegmentFactory()->addSegment('UNH', Unh::class);

        static::assertSame(Unh::class, $segFactory->getClassname('UNH'));
        static::assertInstanceOf(Unh::class, $segFactory->build('UNH+'));
    }

    public function test_using_always_upper_case_as_segment_key(): void
    {
        $segFactory = new ConcreteSegmentFactory()->addSegment('unh', Unh::class);

        static::assertSame(Unh::class, $segFactory->getClassname('UNH'));
        static::assertInstanceOf(Unh::class, $segFactory->build('UNH+'));
    }

    public function test_instanciate_the_fallback_if_set_and_no_specific_segment_was_found(): void
    {
        $segFactory = new ConcreteSegmentFactory()->addFallback(GenericSegment::class);

        static::assertInstanceOf(GenericSegment::class, $segFactory->build('UKW'));
    }

    public function test_throw_an_exception_if_no_fallback_is_set_and_the_segment_is_unknown(): void
    {
        $segFactory = new ConcreteSegmentFactory();

        $this->expectException(InvalidEdifactContentException::class);
        $segFactory->build('UKW');
    }

    public function test_segment_names_are_truncated_to_three_characters(): void
    {
        $segFactory = new ConcreteSegmentFactory()->addSegment('UNHXX', Unh::class);

        static::assertSame(Unh::class, $segFactory->getClassname('UNH'));
    }

    public function test_classes_not_implementing_the_segment_interface_are_ignored(): void
    {
        // @mago-expect analysis:invalid-argument -- deliberately passes a non-Segment class
        $segFactory = new ConcreteSegmentFactory()->addSegment('UNH', stdClass::class);

        $this->expectException(InvalidEdifactContentException::class);
        $segFactory->getClassname('UNH');
    }

    public function test_the_una_segment_is_used_for_parsing(): void
    {
        $segFactory = new ConcreteSegmentFactory()->addFallback(GenericSegment::class);
        $segFactory->setUnaSegment(new UnaSegment('|', '#'));

        $segment = $segFactory->build('SEG#a|b');

        static::assertSame('a', $segment->getValueFromPosition(1, 0));
        static::assertSame('b', $segment->getValueFromPosition(1, 1));
    }
}

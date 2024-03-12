<?php

declare(strict_types=1);

namespace Tests\Segment;

use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Test\TestCase;

final class GenericSegmentTest extends TestCase
{
    public function test_instantiate_from_static_constructor(): void
    {
        $segment = GenericSegment::fromAttributes('UNB', ['1', '2'], ['3']);

        $this->assertInstanceOf(GenericSegment::class, $segment);
        $this->assertSame($segment->getValue('1', '1'), '1');
        $this->assertSame($segment->getValue('1', '2'), '2');
        $this->assertSame($segment->getValue('2', '1'), '3');
    }
}

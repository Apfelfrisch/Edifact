<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\StreamMessageFactory;

final class StreamMessageFactoryTest extends TestCase
{
    public function test_messages_from_the_same_factory_keep_their_own_una(): void
    {
        $factory = new StreamMessageFactory()->addFallback(GenericSegment::class);

        $message = $factory->fromString("RFF+Z13:17103'");
        $factory->fromString('UNA|#,? _RFF#Z13|17103_');

        static::assertSame("RFF+Z13:17103'", $message->toString());
    }

    public function test_messages_with_the_same_una_share_the_segment_factory(): void
    {
        $factory = new StreamMessageFactory()->addFallback(GenericSegment::class);

        $first = $factory->fromString("RFF+Z13:17103'");
        $second = $factory->fromString("RFF+Z13:42'");

        static::assertSame("RFF+Z13:17103'", $first->toString());
        static::assertSame("RFF+Z13:42'", $second->toString());
    }
}

<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Iterators\Stream\Stream;
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

    public function test_building_a_message_from_an_existing_stream(): void
    {
        $factory = new StreamMessageFactory()->addFallback(GenericSegment::class);

        $message = $factory->fromStream(Stream::fromString('UNA|#,? _RFF#Z13|17103_'));

        static::assertSame('RFF#Z13|17103_', $message->toString());
        static::assertSame('_', $message->getUnaSegment()->segmentTerminator());
    }

    public function test_building_messages_from_unwrapped_streams(): void
    {
        $factory = new StreamMessageFactory()->addFallback(GenericSegment::class);
        $stream = Stream::fromString("UNA:+.? 'UNH+1'NAD+Rott?''UNT+1'UNH+2'UNT+2'");

        $messages = [];
        foreach ($stream->unwrap() as $partial) {
            $messages[] = $factory->fromStream($partial)->toString();
        }

        static::assertSame(["UNH+1'NAD+Rott?''UNT+1'", "UNH+2'UNT+2'"], $messages);
    }

    public function test_stream_filters_are_not_applied_to_a_given_stream(): void
    {
        $factory = new StreamMessageFactory()
            ->addFallback(GenericSegment::class)
            ->addStreamFilter('convert.iconv.ISO-8859-1.UTF-8');

        $message = $factory->fromStream(Stream::fromString("NAD+äöü'"));

        static::assertSame("NAD+äöü'", $message->toString());
    }

    public function test_stream_filters_are_applied_to_streams_opened_by_the_factory(): void
    {
        $factory = new StreamMessageFactory()
            ->addFallback(GenericSegment::class)
            ->addStreamFilter('convert.iconv.ISO-8859-1.UTF-8');

        $message = $factory->fromString("NAD+\xE4\xF6\xFC'");

        static::assertSame("NAD+äöü'", $message->toString());
    }
}

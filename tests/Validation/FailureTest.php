<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;

final class FailureTest extends TestCase
{
    public function test_instantiate_with_default_message_and_unh_count(): void
    {
        $failure = new Failure(Failure::MISSING_COMPONENT, 'TST', 5, 10, 'TestValue', 'TestText');

        static::assertSame(Failure::MISSING_COMPONENT, $failure->type);
        static::assertSame('TST', $failure->segmentName);
        static::assertSame(5, $failure->elementPosition);
        static::assertSame(10, $failure->componentPosition);
        static::assertSame('TestValue', $failure->value);
        static::assertSame('TestText', $failure->message);
        static::assertSame(0, $failure->messageCounter);
        static::assertSame(0, $failure->unhCounter);
    }

    public function test_setting_counters_returns_a_new_instance(): void
    {
        $failure = new Failure(Failure::MISSING_COMPONENT, 'TST', 5, 10, 'TestValue', 'TestText');

        $counted = $failure->withCounters(2, 5);

        static::assertNotSame($failure, $counted);
        static::assertSame(0, $failure->messageCounter);
        static::assertSame(0, $failure->unhCounter);
        static::assertSame(2, $counted->messageCounter);
        static::assertSame(5, $counted->unhCounter);
        static::assertSame($failure->type, $counted->type);
        static::assertSame($failure->segmentName, $counted->segmentName);
    }
}

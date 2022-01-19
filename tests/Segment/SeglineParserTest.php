<?php

declare(strict_types = 1);

namespace Tests\Segment;

use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Test\TestCase;

final class SeglineParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider seglineStringProvider()
     */
    public function test_escaping_string(string $string, string $result): void
    {
        $parser = new SeglineParser;

        $this->assertEquals($result, json_encode($parser->parse($string)->toArray()));
    }

    public function seglineStringProvider(): iterable
    {
        yield 'simple-terminate' => [
            'string' => 'A+?:?+',
            'result' => '[["A"],[":+"]]'
        ];

        yield 'simple-terminate-control-chars' => [
            'string' => 'A+?:?+',
            'result' => '[["A"],[":+"]]'
        ];

        yield 'double-terminate-control-chars' => [
            'string' => 'A+??:??+',
            'result' => '[["A"],["??","??"]]'
        ];
    }
}

<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Segment\Elements;
use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SeglineParserTest extends TestCase
{
    public function test_parse_numeric_value(): void
    {
        $unaSegment = new UnaSegment(decimalPoint: ',');
        $parser = new SeglineParser($unaSegment);

        $elements = new Elements()
            ->addValue('A', 'A', 'M|n|3')
            ->addValue('B', 'B', 'M|a|3');
        $string = '1,0+1,1+1,2';

        // Marked as numeric
        static::assertSame('1.0', $parser->parseToBlueprint($string, $elements)->getValue('A', 'A'));

        // Marked as string
        static::assertSame('1,1', $parser->parseToBlueprint($string, $elements)->getValue('B', 'B'));

        // Unmarked
        static::assertSame('1,2', $parser->parseToBlueprint($string, $elements)->getValue('unknown-2', 'unknown-0'));
    }

    public function test_escaping_with_utf8(): void
    {
        $unaSegment = new UnaSegment(':', '🙂');
        $parser = new SeglineParser($unaSegment);
        $string = 'a🙂?🙂';

        $elements = new Elements()
            ->addValue('A', 'A', 'M|a|1')
            ->addValue('B', 'B', 'M|a|1');

        static::assertSame('a', $parser->parseToBlueprint($string, $elements)->getValue('A', 'A'));
        static::assertSame('🙂', $parser->parseToBlueprint($string, $elements)->getValue('B', 'B'));
    }

    #[DataProvider('seglineStringProviderForStringEscaping')]
    public function test_escaping_string(string $string, string $result): void
    {
        $parser = new SeglineParser();

        static::assertSame($result, json_encode($parser->parse($string)->toArray(), JSON_UNESCAPED_UNICODE));
    }

    #[DataProvider('seglineStringProviderForBlueprintParsing')]
    public function test_parse_to_blueprint(string $string, Elements $elements, string $result): void
    {
        $parser = new SeglineParser();

        static::assertSame($result, json_encode($parser->parseToBlueprint($string, $elements)->toArray()));
    }

    /**
     * @return iterable<string, array{string: string, result: string}>
     */
    public static function seglineStringProviderForStringEscaping(): iterable
    {
        yield 'simple-terminate-control-chars' => [
            'string' => 'A+?:?+',
            'result' => '[["A"],[":+"]]',
        ];

        yield 'double-terminate-control-chars' => [
            'string' => 'A+??:???+',
            'result' => '[["A"],["?","?+"]]',
        ];

        yield 'triple-terminate-control-chars' => [
            'string' => 'A+??????:B',
            'result' => '[["A"],["???","B"]]',
        ];
    }

    /**
     * @return iterable<string, array{string: string, elements: Elements, result: string}>
     */
    public static function seglineStringProviderForBlueprintParsing(): iterable
    {
        yield 'simple-terminate-control-chars' => [
            'string' => 'A+?:?+',
            'elements' => new Elements()
                ->addValue('A', 'A', 'M|a|1')
                ->addValue('B', 'B', 'M|a|2'),
            'result' => '{"A":{"A":"A"},"B":{"B":":+"}}',
        ];

        yield 'double-terminate-control-chars' => [
            'string' => 'A+??:???+B',
            'elements' => new Elements()
                ->addValue('A', '1', 'M|a|1')
                ->addValue('B', '1', 'M|a|2')
                ->addValue('B', '2', 'M|a|1')
                ->addValue('C', '1', 'M|a|1'),
            'result' => '{"A":{"1":"A"},"B":{"1":"?","2":"?+B"}}',
        ];

        yield 'triple-terminate-control-chars' => [
            'string' => 'A+??????:B',
            'elements' => new Elements()
                ->addValue('A', 'A', 'M|a|1')
                ->addValue('B', '1', 'M|a|3')
                ->addValue('B', '2', 'M|a|1'),
            'result' => '{"A":{"A":"A"},"B":{"1":"???","2":"B"}}',
        ];

        yield 'unknown-element' => [
            'string' => 'A+B+C:1',
            'elements' => new Elements()
                ->addValue('A', 'A', 'M|a|1')
                ->addValue('B', '1', 'M|a|1')
                ->addValue('C', '1', 'M|a|1'),
            'result' => '{"A":{"A":"A"},"B":{"1":"B"},"C":{"1":"C","unknown-1":"1"}}',
        ];

        yield 'unknown-component' => [
            'string' => 'A+B+C',
            'elements' => new Elements()
                ->addValue('A', 'A', 'M|a|1')
                ->addValue('B', '1', 'M|a|1'),
            'result' => '{"A":{"A":"A"},"B":{"1":"B"},"unknown-2":{"unknown-0":"C"}}',
        ];
    }

    public function test_removing_control_characters(): void
    {
        $parser = new SeglineParser();
        $elements = $parser->parse("SEG+\r\nfoo\n");

        static::assertSame('SEG', $elements->getValueFromPosition(0, 0));
        static::assertSame('foo', $elements->getValueFromPosition(1, 0));
    }
}

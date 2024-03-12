<?php

declare(strict_types=1);

namespace Tests\Segment;

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

        $elements = (new Elements())->addValue('A', 'A', 'M|n|3')->addValue('B', 'B', 'M|a|3');
        $string = '1,0+1,1+1,2';

        // Marked as Numeric
        $this->assertSame('1.0', $parser->parseToBlueprint($string, $elements)->getValue('A', 'A'));

        // Marked as String
        $this->assertSame('1,1', $parser->parseToBlueprint($string, $elements)->getValue('B', 'B'));

        // Umarked
        $this->assertSame('1,2', $parser->parseToBlueprint($string, $elements)->getValue('unknown-2', 'unknown-0'));
    }

    public function test_escaping_with_utf8(): void
    {
        $unaSegment = new UnaSegment(':', '🙂');
        $parser = new SeglineParser($unaSegment);
        $string = 'a🙂?🙂';

        $elements = (new Elements())->addValue('A', 'A', 'M|a|1')->addValue('B', 'B', 'M|a|1');

        $this->assertSame('a', $parser->parseToBlueprint($string, $elements)->getValue('A', 'A'));
        $this->assertSame('🙂', $parser->parseToBlueprint($string, $elements)->getValue('B', 'B'));
    }

    #[DataProvider('seglineStringProviderForStringEscaping')]
    public function test_escaping_string(string $string, string $result): void
    {
        $parser = new SeglineParser();

        $this->assertEquals(
            $result,
            json_encode($parser->parse($string)->toArray(), JSON_UNESCAPED_UNICODE)
        );
    }

    #[DataProvider('seglineStringProviderForBlueprintParsing')]
    public function test_parse_to_blueprint(string $string, Elements $elements, string $result): void
    {
        $parser = new SeglineParser();

        $this->assertEquals($result, json_encode($parser->parseToBlueprint($string, $elements)->toArray()));
    }

    /**
     * @psalm-return iterable<string, array{string: string, result: string}>
     */
    static public function seglineStringProviderForStringEscaping(): iterable
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
     * @psalm-return iterable<string, array{string: string, elements: Elements, result: string}>
     */
    static public function seglineStringProviderForBlueprintParsing(): iterable
    {
        yield 'simple-terminate-control-chars' => [
            'string' => 'A+?:?+',
            'elements' => (new Elements())->addValue('A', 'A', 'M|a|1')->addValue('B', 'B', 'M|a|2'),
            'result' => '{"A":{"A":"A"},"B":{"B":":+"}}',
        ];

        yield 'double-terminate-control-chars' => [
            'string' => 'A+??:???+B',
            'elements' => (new Elements())
                ->addValue('A', '1', 'M|a|1')
                ->addValue('B', '1', 'M|a|2')
                ->addValue('B', '2', 'M|a|1')
                ->addValue('C', '1', 'M|a|1'),
            'result' => '{"A":{"1":"A"},"B":{"1":"?","2":"?+B"}}',
        ];

        yield 'triple-terminate-control-chars' => [
            'string' => 'A+??????:B',
            'elements' => (new Elements())
                ->addValue('A', 'A', 'M|a|1')
                ->addValue('B', '1', 'M|a|3')
                ->addValue('B', '2', 'M|a|1'),
            'result' => '{"A":{"A":"A"},"B":{"1":"???","2":"B"}}',
        ];

        yield 'unkown-element' => [
            'string' => 'A+B+C:1',
            'elements' => (new Elements())
                ->addValue('A', 'A', 'M|a|1')
                ->addValue('B', '1', 'M|a|1')
                ->addValue('C', '1', 'M|a|1'),
            'result' => '{"A":{"A":"A"},"B":{"1":"B"},"C":{"1":"C","unknown-1":"1"}}',
        ];

        yield 'unkown-component' => [
            'string' => 'A+B+C',
            'elements' => (new Elements())
                ->addValue('A', 'A', 'M|a|1')
                ->addValue('B', '1', 'M|a|1'),
            'result' => '{"A":{"A":"A"},"B":{"1":"B"},"unknown-2":{"unknown-0":"C"}}',
        ];
    }
}

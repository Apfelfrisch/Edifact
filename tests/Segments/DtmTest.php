<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\SeglineParser;
use DateTime;
use Iterator;
use Apfelfrisch\Edifact\Segments\Dtm;
use Apfelfrisch\Edifact\Test\TestCase;

final class DtmTest extends TestCase
{
    /** @test */
    public function test_dtm_segment(): void
    {
        $seg = Dtm::fromAttributes('102', '20200101', '102');

        $this->assertEquals('DTM', $seg->name());
        $this->assertEquals('102', $seg->code());
        $this->assertInstanceOf(DateTime::class, $date = $seg->date());
        $this->assertEquals('2020-01-01', $date->format('Y-m-d'));
        $this->assertEquals('20200101', $seg->rawDate());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }

    /**
     * @dataProvider dateCodesProvider
     */
    public function test_pasing_date_codes(string $code, string $dateFormat, string $createFormat): void
    {
        $date = DateTime::createFromFormat("!$createFormat", date($createFormat));

        $seg = Dtm::fromAttributes('137', $date, $code);

        $this->assertEquals($date, $seg->date());
        $this->assertSame($date->format($dateFormat), $seg->rawDate());
    }

    public function test_pasing_raw_date_codes(): void
    {
        foreach (['802', 'Z01'] as $rawDateCode) {
            $rawDate = 'raw-date';
            $seg = Dtm::fromAttributes('137', $rawDate, $rawDateCode);

            $this->assertEquals($rawDate, $seg->date());
            $this->assertSame($rawDate, $seg->rawDate());
        }
    }

    /** @test */
    public function test_throw_an_exception_if_the_date_code_is_unknown(): void
    {
        $code = '999';
        $date = new DateTime;

        $this->expectException(EdifactException::class);
        Dtm::fromAttributes('137', $date, $code);
    }

    /** @psalm-return Iterator<array-key, array<string, string>> */
    public function dateCodesProvider(): Iterator
    {
        yield '102' => [
            'code' => '102',
            'dateFormat' => 'Ymd',
            'createFormat' => 'Y-m-d',
        ];

        yield '106' => [
            'code' => '106',
            'dateFormat' => 'md',
            'createFormat' => 'Y-m-d',
        ];

        yield '203' => [
            'code' => '203',
            'dateFormat' => 'YmdHi',
            'createFormat' => 'Y-m-d-H:i',
        ];

        yield '303' => [
            'code' => '303',
            'dateFormat' => 'YmdHi+00',
            'createFormat' => 'Y-11-d-H:i',
        ];

        yield '602' => [
            'code' => '602',
            'dateFormat' => 'Y',
            'createFormat' => 'Y',
        ];

        yield '610' => [
            'code' => '610',
            'dateFormat' => 'Ym',
            'createFormat' => 'Ym',
        ];
    }
}

<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Cci;
use Proengeno\Edifact\Test\TestCase;

final class CciTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Cci::fromAttributes(new Delimiter(), 'TYP', 'COD', 'MARK', 'LST', 'RES');

        $this->assertEquals('CCI', $seg->name());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('MARK', $seg->mark());
        $this->assertEquals('LST', $seg->codeList());
        $this->assertEquals('RES', $seg->codeResponsible());
        $this->assertEquals($seg->toString(), Cci::fromSegLine(new Delimiter(), $seg->toString()));
    }
}

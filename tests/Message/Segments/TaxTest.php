<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Tax;
use Apfelfrisch\Edifact\Test\TestCase;

final class TaxTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Tax::fromAttributes('QUL', 'TYP', 'RATE-ONE', 'CAT');

        $this->assertEquals('TAX', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('CAT', $seg->category());
        $this->assertEquals('RATE-ONE', $seg->rate());
        $this->assertEquals('CAT', $seg->category());
        $this->assertEquals($seg->toString($delimiter), Tax::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}

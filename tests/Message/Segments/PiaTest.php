<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Pia;
use Apfelfrisch\Edifact\Test\TestCase;

final class PiaTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Pia::fromAttributes('500', 'ARTICLE_NO', 'COD');

        $this->assertEquals('PIA', $seg->name());
        $this->assertEquals('500', $seg->number());
        $this->assertEquals('ARTICLE_NO', $seg->articleNumber());
        $this->assertEquals('COD', $seg->articleCode());
        $this->assertEquals($seg->toString($delimiter), Pia::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}

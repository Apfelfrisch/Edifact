<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Pia;
use Proengeno\Edifact\Test\TestCase;

final class PiaTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pia::fromAttributes(new Delimiter(), '500', 'ARTICLE_NO', 'COD');

        $this->assertEquals('PIA', $seg->name());
        $this->assertEquals('500', $seg->number());
        $this->assertEquals('ARTICLE_NO', $seg->articleNumber());
        $this->assertEquals('COD', $seg->articleCode());
        $this->assertEquals($seg->toString(), Pia::fromSegLine(new Delimiter(), $seg->toString()));
    }
}

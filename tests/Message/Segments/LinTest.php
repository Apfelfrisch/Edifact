<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Lin;
use Proengeno\Edifact\Test\TestCase;

final class LinTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Lin::fromAttributes(new Delimiter(), '123456', 'ARTICLE_NO_1', 'COD');

        $this->assertEquals('LIN', $seg->name());
        $this->assertEquals('123456', $seg->number());
        $this->assertEquals('ARTICLE_NO_1', $seg->articleNumber());
        $this->assertEquals('COD', $seg->articleCode());
        $this->assertEquals($seg->toString(), Lin::fromSegLine(new Delimiter(), $seg->toString()));
    }
}

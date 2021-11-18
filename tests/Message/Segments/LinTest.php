<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Lin;
use Proengeno\Edifact\Test\TestCase;

final class LinTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Lin::fromAttributes('123456', 'ARTICLE_NO_1', 'COD');

        $this->assertEquals('LIN', $seg->name());
        $this->assertEquals('123456', $seg->number());
        $this->assertEquals('ARTICLE_NO_1', $seg->articleNumber());
        $this->assertEquals('COD', $seg->articleCode());
        $this->assertEquals($seg->toString($delimiter), Lin::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}

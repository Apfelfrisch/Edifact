<?php

namespace Code\Packages\Edifact\tests\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\SegmentDescription;
use Proengeno\Edifact\Exceptions\SegmentDesciptionException;

class SegmentDescriptionTest extends TestCase
{
    private $segDescription;

    protected function setUp()
    {
        $this->segDescription = new SegmentDescription(__DIR__ . '/../Fixtures/Segments/meta/dummy.json');
    }

    /** @test */
    public function it_returns_the_metadata()
    {
        $this->assertEquals('DUMMY_NAME', $this->segDescription->name('dummyMethod', 'dummyKey'));
        $this->assertEquals('Dummy description', $this->segDescription->description('dummyMethod', 'dummyKey'));
        $this->assertEquals(['dummy_tag'], $this->segDescription->tags('dummyMethod', 'dummyKey'));
    }

    /** @test */
    public function it_throws_an_exception_if_the_name_for_the_given_method_does_not_exists()
    {
        $this->expectException(SegmentDesciptionException::class);
        $this->assertEquals('DUMMY_NAME', $this->segDescription->name('invaild_method', 'dummyKey'));
    }
}

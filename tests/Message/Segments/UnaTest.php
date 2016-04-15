<?php

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Una;

class UnaTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'UNA';
        $data = 'a';
        $dataGroup ='b';
        $decimal = 'c';
        $terminator = 'd';
        $empty = 'e';

        $seg = Una::fromAttributes($data, $dataGroup, $decimal, $terminator, $empty);
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($data, $seg->data());
        $this->assertEquals($dataGroup, $seg->dataGroup());
        $this->assertEquals($decimal, $seg->decimal());
        $this->assertEquals($terminator, $seg->terminator());
        $this->assertEquals($empty, $seg->emptyChar());

        return $seg;
    }

    /**
     * @test
     * @depends it_can_set_and_fetch_basic_informations
     */
    public function it_the_delimiter_to_the_given_values($seg)
    {
        $delimiter = $seg::getDelimiter();

        $this->assertEquals('a', $delimiter->getData());
        $this->assertEquals('b', $delimiter->getDataGroup());
        $this->assertEquals('c', $delimiter->getDecimal());
        $this->assertEquals('d', $delimiter->getTerminator());
        $this->assertEquals('e', $delimiter->getEmpty());
    }

    /** @test */
    public function it_set_standards_if_not_atrributes_where_given()
    {
        $segName = 'UNA';
        $data = ':';
        $dataGroup ='+';
        $decimal = '.';
        $terminator = '?';
        $empty = ' ';
        $segment = '\'';

        $seg = Una::fromAttributes();
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($data, $seg->data());
        $this->assertEquals($dataGroup, $seg->dataGroup());
        $this->assertEquals($decimal, $seg->decimal());
        $this->assertEquals($terminator, $seg->terminator());
        $this->assertEquals($empty, $seg->emptyChar());
    }

    /** 
     * Una has its own Mapping Implemantation, so we have to test it
     * @test 
     */
    public function it_can_parse_an_edifact_string()
    {
        // 
        $seg = Una::fromSegLine('UNA:+.? ');

        $this->assertEquals('UNA', $seg->name());
        $this->assertEquals(':', $seg->data());
        $this->assertEquals('+', $seg->dataGroup());
        $this->assertEquals('.', $seg->decimal());
        $this->assertEquals('?', $seg->terminator());
        $this->assertEquals(' ', $seg->emptyChar());
    }

    /** 
     * Una has its own __toString function, so we have to test it
     * @test 
     */
    public function it_can_return_itself_as_a_string()
    {
        $unaString = 'UNA:+.? '; 

        $seg = Una::fromSegLine($unaString);

        $this->assertEquals($unaString, (string)$seg);
    }
}

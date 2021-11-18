<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Nad;
use Proengeno\Edifact\Test\TestCase;

final class NadTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Nad::fromAttributes(
            new Delimiter(),
            'ABC',
            '12345678901234567890123456789012345',
            'CBA',
            'Refle',
            'Nils',
            'von Hoäcker',
            'zu Konradsen',
            'Dr.',
            'Z01',
            'In der Strasse mit dem längsten namen der Welt',
            '3a',
            'JemgumOrt',
            'Jemgum',
            '26844',
            'Zusatz Infos'
        );

        $this->assertEquals('NAD', $seg->name());
        $this->assertEquals('ABC', $seg->qualifier());
        $this->assertEquals('12345678901234567890123456789012345', $seg->id());
        $this->assertEquals('CBA', $seg->idCode());
        $this->assertEquals('Nils', $seg->firstName());
        $this->assertEquals('Refle', $seg->lastName());
        $this->assertEquals(null, $seg->company());
        $this->assertEquals('von Hoäcker', $seg->additionalName1());
        $this->assertEquals('zu Konradsen', $seg->additionalName2());
        $this->assertEquals('Dr.', $seg->title());
        $this->assertEquals('In der Strasse mit dem längsten namen der Welt', $seg->street());
        $this->assertEquals('3a', $seg->number());
        $this->assertEquals('26844', $seg->zip());
        $this->assertEquals('Jemgum', $seg->city());

        $this->assertEquals($seg->toString(), Nad::fromSegLine(new Delimiter(), $seg->toString()));
    }

    public function test_initialize_from_person_address(): void
    {
        $seg = Nad::fromPersonAdress(
            new Delimiter(),
            'ABC',
            'Refle',
            'Nils',
            'In der Strasse mit dem längsten namen der Welt',
            '3a',
            'Jemgum',
            '26844',
            'Dr.',
            'JemgumOrt',
        );

        $this->assertEquals('NAD', $seg->name());
        $this->assertEquals('Nils', $seg->firstName());
        $this->assertEquals('Refle', $seg->lastName());
        $this->assertEquals(null, $seg->company());
        $this->assertEquals('In der Strasse mit dem längsten namen der Welt', $seg->street());
        $this->assertEquals('3a', $seg->number());
        $this->assertEquals('26844', $seg->zip());
        $this->assertEquals('Jemgum', $seg->city());
        $this->assertEquals('JemgumOrt', $seg->district());
        $this->assertEquals('Dr.', $seg->title());
    }

    /** @test */
    public function test_initialize_from_company_adress_data(): void
    {
        $seg = Nad::fromCompanyAdress(new Delimiter(), 'ABC', 'Company', 'Street', '3a', 'Leer', '26789');

        $this->assertEquals('NAD', $seg->name());
        $this->assertEquals(null, $seg->firstName());
        $this->assertEquals(null, $seg->lastName());
        $this->assertEquals('Company', $seg->company());
        $this->assertEquals('Street', $seg->street());
        $this->assertEquals('3a', $seg->number());
        $this->assertEquals('26789', $seg->zip());
        $this->assertEquals('Leer', $seg->city());
    }

    /** @test */
    public function test_initialize_from_person_data(): void
    {
        $seg = Nad::fromPerson(new Delimiter(), 'ABC', 'Refle', 'Nils', 'Dr.');

        $this->assertEquals('NAD', $seg->name());
        $this->assertEquals('Nils', $seg->firstName());
        $this->assertEquals('Refle', $seg->lastName());
        $this->assertEquals(null, $seg->company());
        $this->assertEquals('Dr.', $seg->title());
    }

    /** @test */
    public function  test_initialize_from_company_data(): void
    {
        $seg = Nad::fromCompany(new Delimiter(), 'ABC', 'Company');

        $this->assertEquals('NAD', $seg->name());
        $this->assertEquals('Company', $seg->company());
        $this->assertEquals(null, $seg->firstName());
        $this->assertEquals(null, $seg->lastName());
    }

    /** @test */
    public function test_initialize_from_adress_data(): void
    {
        $seg = Nad::fromAdress(new Delimiter(), 'ABC', 'Street', '3a', 'Leer', '26789', 'LeerOrt');

        $this->assertEquals('NAD', $seg->name());
        $this->assertEquals('Street', $seg->street());
        $this->assertEquals('3a', $seg->number());
        $this->assertEquals('26789', $seg->zip());
        $this->assertEquals('Leer', $seg->city());
        $this->assertEquals('LeerOrt', $seg->district());
    }

    /** @test */
    public function test_initialize_from_adress_data_with_additional_informations(): void
    {
        $seg = Nad::fromAttributes(
            delimiter: new Delimiter(),
            qualifier: 'ABC',
            street: 'Street',
            number: '3a',
            city: 'Leer',
            zip: '26844',
            district: 'LeerOrt',
            additionalInformation: 'Zusatz Info',
        );

        $this->assertEquals('NAD', $seg->name());
        $this->assertEquals('Street', $seg->street());
        $this->assertEquals('3a', $seg->number());
        $this->assertEquals('26844', $seg->zip());
        $this->assertEquals('Leer', $seg->city());
        $this->assertEquals('LeerOrt', $seg->district());
    }
}

<?php

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Nad;

class NadTest extends TestCase 
{
    private $nadAttributes;

    public function setUp()
    {
        parent::setUp();
        
        $this->nadAttributes = [
            'segName' => 'NAD',
            'qualifier' => 'ABC',
            'id' => '12345678901234567890123456789012345',
            'idCode' => 'CBA',
            'lastName' => 'Refle',
            'firstName' => 'Nils',
            'company' => 'Proengeno Gmbh und Co. Kg und so weiter und sofort',
            'additionalName1' => 'von Hoäcker',
            'additionalName2' => 'zu Konradsen',
            'title' => 'Dr.',
            'partnerType' => 'Z02',
            'street' => 'In der Strasse mit dem längsten namen der Welt',
            'number' => '3a',
            'district' => 'JemgumOrt',
            'city' => 'Jemgum',
            'zip' => '26844'
        ];
    }
    
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        extract($this->nadAttributes);

        $seg = Nad::fromAttributes(
            $qualifier, 
            $id, 
            $idCode, 
            $lastName,
            $firstName,
            $additionalName1,
            $additionalName2,
            $title,
            $partnerType, 
            $street, 
            $number, 
            $district,
            $city,
            $zip
        );
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($qualifier, $seg->qualifier());
        $this->assertEquals($id, $seg->id());
        $this->assertEquals($idCode, $seg->idCode());
        $this->assertEquals($firstName, $seg->firstName());
        $this->assertEquals($lastName, $seg->lastName());
        $this->assertEquals($additionalName1, $seg->additionalName1());
        $this->assertEquals($additionalName2, $seg->additionalName2());
        $this->assertEquals($title, $seg->title());
        $this->assertEquals($street, $seg->street());
        $this->assertEquals($number, $seg->number());
        $this->assertEquals($zip, $seg->zip());
        $this->assertEquals($city, $seg->city());
    }

    /** @test */
    public function it_can_set_only_person_adress_data()
    {
        extract($this->nadAttributes);

        $seg = Nad::fromPersonAdress($qualifier, $lastName, $firstName, $street, $number, $city, $zip, $title, $district);
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($firstName, $seg->firstName());
        $this->assertEquals($lastName, $seg->lastName());
        $this->assertEquals($street, $seg->street());
        $this->assertEquals($number, $seg->number());
        $this->assertEquals($zip, $seg->zip());
        $this->assertEquals($city, $seg->city());
        $this->assertEquals($title, $seg->title());
    }

    /** @test */
    public function it_can_set_only_the_company_adress_data()
    {
        extract($this->nadAttributes);

        $seg = Nad::fromCompanyAdress($qualifier, $company, $street, $number, $city, $zip, $district);
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($company, $seg->company());
        $this->assertEquals($street, $seg->street());
        $this->assertEquals($number, $seg->number());
        $this->assertEquals($zip, $seg->zip());
        $this->assertEquals($city, $seg->city());
    }

    /** @test */
    public function it_can_set_only_the_person_data()
    {
        extract($this->nadAttributes);

        $seg = Nad::fromPerson($qualifier, $lastName, $firstName, $title);
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($firstName, $seg->firstName());
        $this->assertEquals($lastName, $seg->lastName());
        $this->assertEquals($title, $seg->title());
    }

    /** @test */
    public function it_can_set_only_the_company_data()
    {
        extract($this->nadAttributes);

        $seg = Nad::fromCompany($qualifier, $company);
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($company, $seg->company());
    }

    /** @test */
    public function it_can_set_only_the_adress_data()
    {
        extract($this->nadAttributes);

        $seg = Nad::fromAdress($qualifier, $street, $number, $city, $zip, $district);

        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($street, $seg->street());
        $this->assertEquals($number, $seg->number());
        $this->assertEquals($zip, $seg->zip());
        $this->assertEquals($city, $seg->city());
        $this->assertEquals($district, $seg->district());
    }
}

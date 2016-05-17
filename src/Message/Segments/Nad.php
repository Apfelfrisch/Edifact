<?php 

namespace Proengeno\Edifact\Message\Segments;

class Nad extends Segment 
{
    protected static $validationBlueprint = [
        'NAD' => ['NAD' => 'M|an|3'],
        '3035' => ['3035' => 'M|an|3'],
        'C082' => ['3039' => 'D|an|35', '1131' => null, '3055' => 'D|an|3'],
        'C058' => ['3124' => null],
        'C080' => ['3036:1' => 'D|an|70', '3036:2' => 'D|an|70', '3036:3' => 'D|an|70', '3036:4' => 'D|an|70', '3036:5' => 'D|an|70', '3045' => 'D|an|3'],
        'C059' => ['3042:1' => 'D|an|35', '3042:2' => 'D|an|35', '3042:3' => 'D|an|35', '3042:4' => 'D|an|35'],
        '3164' => ['3164' => 'D|an|35'],
        '3251' => ['3251' => 'D|an|17'],
        '3207' => ['3251' => 'D|an|3'],
    ];

    public static function fromAttributes(
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
        $zip,
        $country = 'DE'
    )
    {
        return new static([
            'NAD' => ['NAD' => 'NAD'],
            '3035' => ['3035' => $qualifier],
            'C082' => ['3039' => $id, '1131' => null, '3055' => $idCode],
            'C058' => ['3124' => null],
            'C080' => [
                '3036:1' => $lastName, 
                '3036:2' => $firstName, 
                '3036:3' => $additionalName1, 
                '3036:4' => $additionalName2, 
                '3036:5' => $title, 
                '3045' => $partnerType
            ],
            'C059' => ['3042:1' => substr($street, 0, 35), '3042:2' => substr($street, 35), '3042:3' => $number, '3042:4' => $district],
            '3164' => ['3164' => $city],
            '3251' => ['3251' => $zip],
            '3207' => ['3251' => $country]
        ]);
    }

    public static function fromMpCode($qualifier, $id, $idCode)
    {
        return static::fromAttributes(
            $qualifier, $id, $idCode, null, null, null, null, null, null, null, null, null, null, null, null
        );
    }

    public static function fromPersonAdress($qualifier, $lastName, $firstName, $street, $number, $city, $zip, $title = null, $district = null)
    {
        return static::fromAttributes(
            $qualifier, null, null, $lastName, $firstName, null, null, $title, 'Z01', $street, $number, $district, $city, $zip
        );
    }

    public static function fromCompanyAdress($qualifier, $company, $street, $number, $city, $zip, $title = null, $district = null)
    {
        return static::fromAttributes(
            $qualifier, null, null, substr($company, 0, 70), substr($company, 70), null, null, null, 'Z02', $street, $number, $district, $city, $zip
        );
    }

    public static function fromPerson($qualifier, $lastName, $firstName, $title = null, $additionalName1 = null, $additionalName2 = null)
    {
        return static::fromAttributes(
            $qualifier, null, null, $lastName, $firstName, $additionalName1, $additionalName2, $title, 'Z01', null, null, null, null, null
        );
    }

    public static function fromCompany($qualifier, $company, $additionalName1 = null, $additionalName2 = null)
    {
        return static::fromAttributes(
            $qualifier, null, null, substr($company, 0, 70), substr($company, 70), $additionalName1, $additionalName2, null, 'Z02', null, null, null, null, null
        );
    }

    public static function fromAdress($qualifier, $street, $number, $city, $zip, $district = null)
    {
        return static::fromAttributes(
            $qualifier, null, null, null, null, null, null, null, null, $street, $number, $district, $city, $zip
        );
    }

    public function qualifier()
    {
        return @$this->elements['3035']['3035'] ?: null;
    }

    public function id()
    {
        return @$this->elements['C082']['3039'] ?: null;
    }
    
    public function idCode()
    {
        return @$this->elements['C082']['3055'] ?: null;
    }

    public function street()
    {
        return $this->elements['C059']['3042:1'].$this->elements['C059']['3042:2'];
    }

    public function number()
    {
        return @$this->elements['C059']['3042:3'] ?: null;
    }

    public function district()
    {
        return @$this->elements['C059']['3042:4'] ?: null;
    }

    public function company()
    {
        return $this->lastName().$this->firstName();
    }

    public function firstName()
    {
        return $this->elements['C080']['3036:2'];
    }

    public function lastName()
    {
        return $this->elements['C080']['3036:1'];
    }

    public function additionalName1()
    {
        return $this->elements['C080']['3036:3'];
    }

    public function additionalName2()
    {
        return $this->elements['C080']['3036:4'];
    }

    public function title()
    {
        return $this->elements['C080']['3036:5'];
    }

    public function zip()
    {
        return @$this->elements['3251']['3251'] ?: null;
    }
    
    public function city()
    {
        return @$this->elements['3164']['3164'] ?: null;
    }
}

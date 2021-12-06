<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Nad extends AbstractSegment
{
    const PERSON_ADRESS = 'Z01';
    const COMPANY_ADRESS = 'Z02';

    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('NAD', 'NAD', 'M|an|3')
                ->addValue('3035', '3035', 'M|an|..3')
                ->addValue('C082', '3039', 'O|an|..35')
                ->addValue('C082', '1131', null)
                ->addValue('C082', '3055', 'O|an|..3')
                ->addValue('C058', '3124', null)
                ->addValue('C080', '3036:1', 'O|an|..70')
                ->addValue('C080', '3036:2', 'O|an|..70')
                ->addValue('C080', '3036:3', 'O|an|..70')
                ->addValue('C080', '3036:4', 'O|an|..70')
                ->addValue('C080', '3036:5', 'O|an|..70')
                ->addValue('C080', '3045', 'O|an|..3')
                ->addValue('C059', '3042:1', 'O|an|..35')
                ->addValue('C059', '3042:2', 'O|an|..35')
                ->addValue('C059', '3042:3', 'O|an|..35')
                ->addValue('C059', '3042:4', 'O|an|..35')
                ->addValue('3164', '3164', 'O|an|..35')
                ->addValue('C819', '3229', null)
                ->addValue('3251', '3251', 'O|an|..17')
                ->addValue('3207', '3207', 'O|an|..3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(
        string $qualifier,
        ?string $id = null,
        ?string $idCode = null,
        ?string $lastName = null,
        ?string $firstName = null,
        ?string $additionalName1 = null,
        ?string $additionalName2 = null,
        ?string $title = null,
        ?string $partnerType = null,
        ?string $street = null,
        ?string $number = null,
        ?string $district = null,
        ?string $city = null,
        ?string $zip = null,
        ?string $country = null,
        ?string $additionalInformation = null,
    ): self
    {
        return new self((new Elements)
            ->addValue('NAD', 'NAD', 'NAD')
            ->addValue('3035', '3035', $qualifier)
            ->addValue('C082', '3039', $id)
            ->addValue('C082', '1131', null)
            ->addValue('C082', '3055', $idCode)
            ->addValue('C058', '3124', $additionalInformation)
            ->addValue('C080', '3036:1', $lastName)
            ->addValue('C080', '3036:2', $firstName)
            ->addValue('C080', '3036:3', $additionalName1)
            ->addValue('C080', '3036:4', $additionalName2)
            ->addValue('C080', '3036:5', $title)
            ->addValue('C080', '3045', $partnerType )
            ->addValue('C059', '3042:1', $street !== null ? substr($street, 0, 35) : null)
            ->addValue('C059', '3042:2', $street !== null ? substr($street, 35) : null)
            ->addValue('C059', '3042:3', $number)
            ->addValue('C059', '3042:4', $district)
            ->addValue('3164', '3164', $city)
            ->addValue('C819', '3229', null)
            ->addValue('3251', '3251', $zip)
            ->addValue('3207', '3251', $country)
        );
    }

    public static function fromQualifier(string $qualifier): self
    {
        return self::fromAttributes($qualifier);
    }

    public static function fromMpCode(string $qualifier, string $id, string $idCode): self
    {
        return self::fromAttributes($qualifier, $id, $idCode);
    }

    public static function fromPersonAdress(
        string $qualifier,
        string $lastName,
        string $firstName,
        string $street,
        string $number,
        string $city,
        string $zip,
        ?string $title = null,
        ?string $district = null,
        ?string $additionalInformation = null,
        string $country = 'DE',
    ): self {
        return self::fromAttributes(
            qualifier: $qualifier,
            lastName: $lastName,
            firstName: $firstName,
            title: $title,
            partnerType: self::PERSON_ADRESS,
            street: $street,
            number: $number,
            district: $district,
            city: $city,
            zip: $zip,
            country: $country,
            additionalInformation: $additionalInformation,
        );
    }

    /**
     * @todo Check if $title Attribute is nessecary
     */
    public static function fromCompanyAdress(
        string $qualifier,
        string $company,
        string $street,
        string $number,
        string $city,
        string $zip,
        ?string $title = null,
        ?string $district = null,
        ?string $additionalInformation = null,
        string $country = 'DE',
    ): self {
        return self::fromAttributes(
            qualifier: $qualifier,
            lastName: substr($company, 0, 70),
            firstName: substr($company, 70),
            title: $title,
            partnerType: self::COMPANY_ADRESS,
            street: $street,
            number: $number,
            district: $district,
            city: $city,
            zip: $zip,
            country: $country,
            additionalInformation: $additionalInformation
        );
    }

    public static function fromPerson(
        string $qualifier,
        string $lastName,
        string $firstName,
        ?string $title = null,
        ?string $additionalName1 = null,
        ?string $additionalName2 = null
    ): self
    {
        return static::fromAttributes(
            $qualifier, null, null, $lastName, $firstName, $additionalName1, $additionalName2, $title, self::PERSON_ADRESS
        );
    }

    public static function fromCompany(string $qualifier, string $company, ?string $additionalName1 = null, ?string $additionalName2 = null): self
    {
        return self::fromAttributes(
            $qualifier, null, null, substr($company, 0, 70), substr($company, 70), $additionalName1, $additionalName2, null, self::COMPANY_ADRESS
        );
    }

    public static function fromAdress(
        string $qualifier,
        string $street,
        string $number,
        string $city,
        string $zip,
        ?string $district = null,
        string $country = 'DE',
        ?string $additionalInformation = null,
    ): self
    {
        return self::fromAttributes(
            qualifier: $qualifier,
            street: $street,
            number: $number,
            city: $city,
            zip: $zip,
            country: $country,
            district: $district,
            additionalInformation: $additionalInformation
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('3035', '3035');
    }

    public function id(): ?string
    {
        return $this->elements->getValue('C082', '3039');
    }

    public function idCode(): ?string
    {
        return $this->elements->getValue('C082', '3055');
    }

    public function street(): ?string
    {
        $streetPartOne = $this->elements->getValue('C059', '3042:1');
        $streetPartTwo = $this->elements->getValue('C059', '3042:2');

        if (null === $streetPartOne) {
            if (null === $streetPartTwo) {
                return null;
            }
            return $streetPartTwo;
        }

        return $streetPartOne . (string)$streetPartTwo;
    }

    public function number(): ?string
    {
        return $this->elements->getValue('C059', '3042:3');
    }

    public function district(): ?string
    {
        return $this->elements->getValue('C059', '3042:4');
    }

    public function company(): ?string
    {
        if ($this->partnerType() != self::COMPANY_ADRESS) {
            return null;
        }

        return (string)$this->elements->getValue('C080', '3036:1') . (string)$this->elements->getValue('C080', '3036:2');
    }

    public function firstName(): ?string
    {
        if ($this->partnerType() != self::PERSON_ADRESS) {
            return null;
        }

        return $this->elements->getValue('C080', '3036:2');
    }

    public function lastName(): ?string
    {
        if ($this->partnerType() != self::PERSON_ADRESS) {
            return null;
        }

        return $this->elements->getValue('C080', '3036:1');
    }

    public function additionalName1(): ?string
    {
        return $this->elements->getValue('C080', '3036:3');
    }

    public function additionalName2(): ?string
    {
        return $this->elements->getValue('C080', '3036:4');
    }

    public function title(): ?string
    {
        return $this->elements->getValue('C080', '3036:5');
    }

    public function partnerType(): ?string
    {
        return $this->elements->getValue('C080', '3045');
    }

    public function zip(): ?string
    {
        return $this->elements->getValue('3251', '3251');
    }

    public function city(): ?string
    {
        return $this->elements->getValue('3164', '3164');
    }
}

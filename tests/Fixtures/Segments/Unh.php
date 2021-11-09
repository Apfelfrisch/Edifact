<?php

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Templates\AbstractSegment;

class Unh extends AbstractSegment
{
    protected static $validationBlueprint = [
        'UNH' => ['UNH' => 'M|an|3'],
        '0062' => ['0062' => 'M|an|14'],
        'S009' => ['0065' => 'M|an|6', '0052' => 'M|an|3', '0054' => 'M|an|3', '0051' => 'M|an|2', '0057' => 'M|an|6'],
    ];

    public static function fromAttributes($referenz, $type, $versionNumber, $releaseNumber, $organisation, $organisationCode)
    {
        return new static(
            (new DataGroupCollection(static::getBuildDelimiter()))
                ->addValue('UNH', 'UNH', 'UNH')
                ->addValue('0062', '0062', $referenz)
                ->addValue('S009', '0065', $type)
                ->addValue('S009', '0052', $versionNumber)
                ->addValue('S009', '0054', $releaseNumber)
                ->addValue('S009', '0051', $organisation)
                ->addValue('S009', '0057', $organisationCode)
        );
    }

    public function referenz()
    {
        return $this->elements->getValue('0062', '0062');
    }

    public function type()
    {
        return @$this->elements['S009']['0065'] ?: null;
    }

    public function versionNumber()
    {
        return @$this->elements['S009']['0052'] ?: null;
    }

    public function releaseNumber()
    {
        return @$this->elements['S009']['0054'] ?: null;
    }

    public function organisation()
    {
        return @$this->elements['S009']['0051'] ?: null;
    }

    public function organisationCode()
    {
        return @$this->elements['S009']['0057'] ?: null;
    }
}

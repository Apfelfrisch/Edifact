<?php 

namespace Proengeno\Edifact\Test\Fixtures\Segments;

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
        return new static([
            'UNH' => ['UNH' => 'UNH'],
            '0062' => ['0062' => $referenz],
            'S009' => ['0065' => $type, '0052' => $versionNumber, '0054' => $releaseNumber, '0051' => $organisation, '0057' => $organisationCode],
        ]);
    }

    public function referenz()
    {
        return @$this->elements['0062']['0062'] ?: null;
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

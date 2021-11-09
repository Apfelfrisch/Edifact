<?php

namespace Proengeno\Edifact\Test\Fixtures\Segments;

use DateTime;
use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Templates\AbstractSegment;

class Unb extends AbstractSegment
{
    protected static $validationBlueprint = [
        'UNB' => ['UNB' => 'M|an|3'],
        'S001' => ['0001' => 'M|a|4', '0002' => 'm|n|1'],
        'S002' => ['0004' => 'M|an|35', '0007' => 'M|an|4'],
        'S003' => ['0010' => 'M|an|35', '0007' => 'M|an|4'],
        'S004' => ['0017' => 'M|n|6', '0019' => 'M|n|4'],
        '0020' => ['0020' => 'M|an|14'],
        '0026' => ['0026' => 'O|an|14'],
        '0035' => ['0035' => 'O|n|1'],
    ];

    public static function fromAttributes($syntaxId, $syntaxVersion, $sender, $senderQualifier, $receiver, $receiverQualifier, DateTime $creationDatetime, $referenzNumber, $usageType = null, $testMarker = null)
    {
        return new static(
            (new DataGroupCollection(static::getBuildDelimiter()))
                ->addValue('UNB', 'UNB', 'UNB')
                ->addValue('S001', '0001', $syntaxId)
                ->addValue('S001', '0002', $syntaxVersion)
                ->addValue('S002', '0004', $sender)
                ->addValue('S002', '0007', $senderQualifier)
                ->addValue('S003', '0010', $receiver)
                ->addValue('S003', '0007', $receiverQualifier)
                ->addValue('S004', '0017', $creationDatetime->format('ymd'))
                ->addValue('0020', '0020', $referenzNumber)
                ->addValue('0026', '0026', $usageType)
                ->addValue('0035', '0035', $usageType)
        );
    }

    public function syntaxId()
    {
        return @$this->elements['S001']['0001'] ?: null;
    }

    public function syntaxVersion()
    {
        return @$this->elements['S001']['0002'] ?: null;
    }

    public function sender()
    {
        return @$this->elements['S002']['0004'] ?: null;
    }

    public function senderQualifier()
    {
        return @$this->elements['S002']['0007'] ?: null;
    }

    public function receiver()
    {
        return @$this->elements['S003']['0010'] ?: null;
    }

    public function receiverQualifier()
    {
        return @$this->elements['S003']['0007'] ?: null;
    }

    public function creationDateTime()
    {
        return DateTime::createFromFormat('ymdhi', $this->elements['S004']['0017'].$this->elements['S004']['0019']);
    }

    public function referenzNumber()
    {
        return @$this->elements['0020']['0020'] ?: null;
    }

    public function usageType()
    {
        return @$this->elements['0026']['0026'] ?: null;
    }

    public function testMarker()
    {
        return @$this->elements['0035']['0035'] ?: null;
    }
}

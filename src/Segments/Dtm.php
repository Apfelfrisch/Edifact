<?php

namespace Apfelfrisch\Edifact\Segments;

use DateTime;
use Apfelfrisch\Edifact\DataGroups;
use Apfelfrisch\Edifact\Exceptions\SegValidationException;

class Dtm extends AbstractSegment
{
    private static ?DataGroups $blueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new DataGroups)
                ->addValue('DTM', 'DTM', 'M|a|3')
                ->addValue('C507', '2005', 'M|an|3')
                ->addValue('C507', '2380', 'M|an|35')
                ->addValue('C507', '2379', 'M|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, DateTime|string $date, string $code): self
    {
        if ($date instanceof DateTime) {
            $date = static::serializeDateTime($date, $code);
        }

        return new self((new DataGroups)
            ->addValue('DTM', 'DTM', 'DTM')
            ->addValue('C507', '2005', $qualifier)
            ->addValue('C507', '2380', $date)
            ->addValue('C507', '2379', $code)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('C507', '2005');
    }

    public function date(): DateTime|string|null
    {
        return $this->buildDate((string)$this->rawDate(), (string)$this->code());
    }

    public function rawDate(): ?string
    {
        return $this->elements->getValue('C507', '2380');
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C507', '2379');
    }

    private static function serializeDateTime(DateTime $date, string $code): string
    {
        return match($code) {
            '102' => $date->format('Ymd'),
            '106' => $date->format('md'),
            '203' => $date->format('YmdHi'),
            '303' => $date->format('YmdHi') . substr($date->format('O'), 0, 3),
            '602' => $date->format('Y'),
            '610' => $date->format('Ym'),
            default => throw SegValidationException::forKeyValue('DTM', $code, "Timecode doesnt Support DateTime or is unknown.")
        };
    }

    private function buildDate(string $string, string $code): DateTime|string
    {
        switch ($code) {
            case 102:
                // If no time is set, it takes the creation time. We dont want that
                $hour = 0;
                return DateTime::createFromFormat('YmdH', $string.$hour);
            case 106:
                // If no time is set, it takes the creation time. We dont want that
                $hour = 0;
                return DateTime::createFromFormat('mdH', $string.$hour);
            case 203:
                return DateTime::createFromFormat('YmdHi', $string);
            case 303:
                return DateTime::createFromFormat('YmdHi', substr($string, 0, -3));
            case 602:
                $month = '01';
                $day = '01';
                $hour = 0;
                return DateTime::createFromFormat('YmdH', $string.$month.$day.$hour);
            case 610:
                $day = '01';
                $hour = 0;
                return DateTime::createFromFormat('YmdH', $string.$day.$hour);
            case 802:
            case 'Z01':
                return $string;
        }

        throw SegValidationException::forKeyValue('DTM', $code, "Timecode unknown.");
    }

    protected function getGetterMethods(): array
    {
        return [
            'qualifier',
            'rawDate',
            'code',
        ];
    }
}

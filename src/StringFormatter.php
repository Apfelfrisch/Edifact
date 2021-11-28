<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Interfaces\FormatterInterface;
use Apfelfrisch\Edifact\Interfaces\SegInterface;

final class StringFormatter implements FormatterInterface
{
    public function __construct(
        private Delimiter $delimiter
    ) { }

    public function format(SegInterface ...$segments): string
    {
        $string = '';

        if ($segments[0]->name() === 'UNA') {
            array_shift($segments);

            $string = 'UNA'
                . $this->delimiter->getComponentSeparator()
                . $this->delimiter->getElementSeparator()
                . $this->delimiter->getDecimalPoint()
                . $this->delimiter->getEscapeCharacter()
                . $this->delimiter->getSpaceCharacter()
                . $this->delimiter->getSegmentTerminator();
        }

        foreach ($segments as $segment) {
            $segmentString = '';
            foreach($segment->toArray() as $element) {
                foreach ($element as $value) {
                    $segmentString .= $value === null
                        ? $this->delimiter->getComponentSeparator()
                        : $this->escapeString($value) . $this->delimiter->getComponentSeparator();
                }

                $segmentString = $this->trimEmpty(
                    $segmentString, $this->delimiter->getComponentSeparator()
                ) . $this->delimiter->getElementSeparator();
            }
            $string .= $this->trimEmpty($segmentString, $this->delimiter->getElementSeparator()) . $this->delimiter->getSegmentTerminator();
        }

        return $string;
    }

    private function escapeString(string $string): string
    {
        return str_replace(
            [
                $this->delimiter->getComponentSeparator(),
                $this->delimiter->getElementSeparator(),
                '\\n'
            ],
            [
                $this->delimiter->getEscapeCharacter() . $this->delimiter->getComponentSeparator(),
                $this->delimiter->getEscapeCharacter() . $this->delimiter->getElementSeparator(),
                ''
            ],
            $string
        );
    }

    private function trimEmpty(string $string, string $seperator): string
    {
        while(true) {
            if ($seperator !== $string[-1] ?? null) {
                break;
            }

            if ($this->delimiter->getEscapeCharacter() === $string[-2] ?? null) {
                break;
            }

            $string = substr($string, 0, -1);
        }

        return $string;
    }
}

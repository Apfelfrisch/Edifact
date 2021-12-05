<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Interfaces\FormatterInterface;
use Apfelfrisch\Edifact\Interfaces\SegInterface;

final class StringFormatter implements FormatterInterface
{
    private bool $prefixUna = false;

    public function __construct(
        private UnaSegment $unaSegment
    ) { }

    public function prefixUna(): self
    {
        $this->prefixUna = true;

        return $this;
    }

    public function format(SegInterface ...$segments): string
    {
        $string = '';

        if ($this->prefixUna) {
            $string = $this->unaSegment->toString();
            $this->prefixUna = false;
        }

        foreach ($segments as $segment) {
            $segmentString = '';
            foreach($segment->toArray() as $element) {
                foreach ($element as $value) {
                    $segmentString .= $value === null
                        ? $this->unaSegment->componentSeparator()
                        : $this->escapeString($value) . $this->unaSegment->componentSeparator();
                }

                $segmentString = $this->trimEmpty(
                    $segmentString, $this->unaSegment->componentSeparator()
                ) . $this->unaSegment->elementSeparator();
            }
            $string .= $this->trimEmpty($segmentString, $this->unaSegment->elementSeparator()) . $this->unaSegment->segmentTerminator();
        }

        return $string;
    }

    private function escapeString(string $string): string
    {
        return str_replace(
            [
                $this->unaSegment->componentSeparator(),
                $this->unaSegment->elementSeparator(),
                '\\n'
            ],
            [
                $this->unaSegment->escapeCharacter() . $this->unaSegment->componentSeparator(),
                $this->unaSegment->escapeCharacter() . $this->unaSegment->elementSeparator(),
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

            if ($this->unaSegment->escapeCharacter() === $string[-2] ?? null) {
                break;
            }

            $string = substr($string, 0, -1);
        }

        return $string;
    }
}

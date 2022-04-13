<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Formatter;

use Apfelfrisch\Edifact\Segment\SegmentInterface;
use Apfelfrisch\Edifact\Segment\UnaSegment;

final class EdifactFormatter implements FormatterInterface
{
    private bool $prefixUna = false;

    public function __construct(
        private UnaSegment $unaSegment
    ) {
    }

    public function prefixUna(): self
    {
        $this->prefixUna = true;

        return $this;
    }

    public function format(SegmentInterface ...$segments): string
    {
        $string = '';

        if ($this->prefixUna) {
            $string = $this->unaSegment->toString();
            $this->prefixUna = false;
        }

        foreach ($segments as $segment) {
            $segmentString = '';
            foreach ($segment->toArray() as $elementKey => $element) {
                foreach ($element as $componentKey => $value) {
                    /** @psalm-suppress RedundantCastGivenDocblockType: Php autocasts to integer */
                    $segmentString .= $this->buildComponent($value, $segment, (string)$elementKey, (string)$componentKey);
                }

                $segmentString = $this->trimEmpty(
                    $segmentString,
                    $this->unaSegment->componentSeparator()
                ) . $this->unaSegment->elementSeparator();
            }
            $string .= $this->trimEmpty($segmentString, $this->unaSegment->elementSeparator()) . $this->unaSegment->segmentTerminator();
        }

        return $string;
    }

    private function buildComponent(string|null $value, SegmentInterface $segment, string $elementKey, string $componentKey): string
    {
        if ($value === null) {
            return $this->unaSegment->componentSeparator();
        }

        if (! $this->unaSegment->usesPhpDecimalPoint() && $segment->isValueNumeric($elementKey, $componentKey)) {
            return str_replace(UnaSegment::PHP_DECIMAL, $this->unaSegment->decimalPoint(), $value);
        }

        if (! $this->unaSegment->usesPhpSpaceCharacter()) {
            $value = str_replace(UnaSegment::PHP_SPACE, $this->unaSegment->spaceCharacter(), $value);
        }

        return $this->escapeString($value) . $this->unaSegment->componentSeparator();
    }

    private function escapeString(string $string): string
    {
        return str_replace(
            [
                $this->unaSegment->escapeCharacter(),
                $this->unaSegment->componentSeparator(),
                $this->unaSegment->elementSeparator(),
                '\\n',
            ],
            [
                $this->unaSegment->escapeCharacter() . $this->unaSegment->escapeCharacter(),
                $this->unaSegment->escapeCharacter() . $this->unaSegment->componentSeparator(),
                $this->unaSegment->escapeCharacter() . $this->unaSegment->elementSeparator(),
                '',
            ],
            $string
        );
    }

    private function trimEmpty(string $string, string $seperator): string
    {
        while ($string !== '') {
            if ($seperator !== $string[-1]) {
                break;
            }

            if ($this->unaSegment->escapeCharacter() === ($string[-2] ?? null)) {
                break;
            }

            $string = substr($string, 0, -1);
        }

        return $string;
    }
}

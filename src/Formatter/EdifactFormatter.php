<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Formatter;

use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\UnaSegment;

final class EdifactFormatter implements Formatter
{
    private bool $prefixUna = false;

    /** @var non-empty-string */
    private readonly string $componentSeparator;

    /** @var non-empty-string */
    private readonly string $elementSeparator;

    /** @var non-empty-string */
    private readonly string $segmentTerminator;

    /** @var non-empty-string */
    private readonly string $escapeCharacter;

    private readonly bool $usesPhpDecimalPoint;

    private readonly bool $usesPhpSpaceCharacter;

    /** @var list<string> */
    private readonly array $escapeSearch;

    /** @var list<string> */
    private readonly array $escapeReplace;

    /** Byte set for a fast strpbrk() pre-check before escaping */
    private readonly string $escapeCheckBytes;

    public function __construct(
        private readonly UnaSegment $unaSegment,
    ) {
        $this->componentSeparator = $unaSegment->componentSeparator();
        $this->elementSeparator = $unaSegment->elementSeparator();
        $this->segmentTerminator = $unaSegment->segmentTerminator();
        $this->escapeCharacter = $unaSegment->escapeCharacter();
        $this->usesPhpDecimalPoint = $unaSegment->usesPhpDecimalPoint();
        $this->usesPhpSpaceCharacter = $unaSegment->usesPhpSpaceCharacter();

        $this->escapeSearch = [
            $this->escapeCharacter,
            $this->componentSeparator,
            $this->elementSeparator,
            $this->segmentTerminator,
            '\\n',
        ];
        $this->escapeReplace = [
            $this->escapeCharacter . $this->escapeCharacter,
            $this->escapeCharacter . $this->componentSeparator,
            $this->escapeCharacter . $this->elementSeparator,
            $this->escapeCharacter . $this->segmentTerminator,
            '',
        ];
        $this->escapeCheckBytes =
            $this->escapeCharacter
            . $this->componentSeparator
            . $this->elementSeparator
            . $this->segmentTerminator
            . '\\';
    }

    public function prefixUna(): self
    {
        $this->prefixUna = true;

        return $this;
    }

    public function format(Segment ...$segments): string
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
                    if ($value === null) {
                        $segmentString .= $this->componentSeparator;

                        continue;
                    }

                    if (
                        !$this->usesPhpDecimalPoint
                        && $segment->isValueNumeric((string) $elementKey, (string) $componentKey)
                    ) {
                        $segmentString .=
                            str_replace(UnaSegment::PHP_DECIMAL, $this->unaSegment->decimalPoint(), $value)
                            . $this->componentSeparator;

                        continue;
                    }

                    if (!$this->usesPhpSpaceCharacter) {
                        $value = str_replace(UnaSegment::PHP_SPACE, $this->unaSegment->spaceCharacter(), $value);
                    }

                    if (strpbrk($value, $this->escapeCheckBytes) !== false) {
                        $value = str_replace($this->escapeSearch, $this->escapeReplace, $value);
                    }

                    $segmentString .= $value . $this->componentSeparator;
                }

                $segmentString = $this->trimEmpty($segmentString, $this->componentSeparator) . $this->elementSeparator;
            }

            $string .= $this->trimEmpty($segmentString, $this->elementSeparator) . $this->segmentTerminator;
        }

        return $string;
    }

    private function trimEmpty(string $string, string $separator): string
    {
        $length = strlen($string);

        while ($length > 0 && $string[$length - 1] === $separator) {
            if ($length > 1 && $string[$length - 2] === $this->escapeCharacter) {
                break;
            }

            $length--;
        }

        return $length === strlen($string) ? $string : substr($string, 0, $length);
    }
}

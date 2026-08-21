<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

/**
 * A syntax failure found while validating the structure of an interchange.
 *
 * Carries all context needed to fill the segments of a CONTRL message:
 * the reporting level (UCI/UCM/UCS/UCD), the UN/EDIFACT error code (0085),
 * the affected service segment (0013), the message counter and reference
 * (UCM), the segment position within the message (UCS 0096) and the
 * element/component positions (UCD 0098/0104).
 */
final readonly class SyntaxFailure
{
    public function __construct(
        public ReportLevel $level,
        public SyntaxErrorCode $errorCode,
        public null|string $serviceSegment = null,
        public int $messageCounter = 0,
        public null|string $messageReference = null,
        public null|int $segmentPosition = null,
        public null|int $elementPosition = null,
        public null|int $componentPosition = null,
        public null|string $value = null,
    ) {}

    public function description(): string
    {
        return $this->errorCode->description();
    }
}

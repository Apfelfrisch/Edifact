<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segment\Segment;

/**
 * Validates the structure of a complete interchange (Übertragungsdatei)
 * the way a CONTRL syntax check requires it (UN/EDIFACT service segments
 * UNB/UNZ and UNH/UNT, references, control counts).
 *
 * The check runs top-down, mirroring the CONTRL rules:
 *
 * - Interchange level (UNB/UNZ): on any failure the check stops and only
 *   the interchange failures are reported.
 * - Message level (UNH/UNT): a failing message reports only its
 *   UNH/UNT failures; its remaining segments are not inspected.
 *   Other messages are still checked.
 * - Segment level: segments implementing Validateable are checked against
 *   their blueprint; failures carry the segment position within the
 *   message (UNH = position 1) and map to UN/EDIFACT error codes.
 *
 * Checks requiring external knowledge (own MP-ID, known senders,
 * already-received interchange references) are the callers concern.
 */
final class InterchangeValidator
{
    private const string UNB = 'UNB';
    private const string UNZ = 'UNZ';
    private const string UNH = 'UNH';
    private const string UNT = 'UNT';

    private readonly SegmentValidator $segmentValidator;

    /** @var list<SyntaxFailure> */
    private array $interchangeFailures = [];

    /** @var array<int, list<SyntaxFailure>> */
    private array $messageFailures = [];

    /** @var array<int, list<SyntaxFailure>> */
    private array $segmentFailures = [];

    /** @var array<string, true> */
    private array $seenMessageReferences = [];

    public function __construct(null|SegmentValidator $segmentValidator = null)
    {
        $this->segmentValidator = $segmentValidator ?? new SegmentValidator();
    }

    /**
     * @return list<SyntaxFailure>
     */
    // @mago-expect lint:halstead
    public function validate(Message $message): array
    {
        $this->interchangeFailures = [];
        $this->messageFailures = [];
        $this->segmentFailures = [];
        $this->seenMessageReferences = [];

        $unbRef = null;
        $unhRef = null;
        $messageCount = 0;
        $segmentPosition = 0;
        $inMessage = false;
        $sawUnb = false;
        $sawUnz = false;
        $sawAnySegment = false;

        foreach ($message->getSegments() as $segment) {
            $name = $segment->name();

            if ($sawUnz) {
                $this->addInterchangeFailure(SyntaxErrorCode::TooManyConstituents, value: $name);

                break;
            }

            if (!$sawAnySegment) {
                $sawAnySegment = true;

                if ($name !== self::UNB) {
                    $this->addInterchangeFailure(SyntaxErrorCode::Missing, self::UNB);
                }
            }

            if ($name === self::UNB) {
                if ($sawUnb || $inMessage || $messageCount > 0) {
                    $this->addInterchangeFailure(SyntaxErrorCode::NotSupportedAtPosition, self::UNB);

                    continue;
                }

                $sawUnb = true;
                $unbRef = $segment->getValueFromPosition(5, 0);

                if ($unbRef === null || $unbRef === '') {
                    $this->addInterchangeFailure(SyntaxErrorCode::Missing, self::UNB);
                }

                continue;
            }

            if ($name === self::UNH) {
                if ($inMessage) {
                    $this->addMessageFailure($messageCount, SyntaxErrorCode::Missing, self::UNT, $unhRef);
                }

                $messageCount++;
                $inMessage = true;
                $segmentPosition = 1;
                $unhRef = $segment->getValueFromPosition(1, 0);
                $this->registerMessageReference($unhRef, $messageCount);

                continue;
            }

            if ($name === self::UNT) {
                if (!$inMessage) {
                    $this->addInterchangeFailure(SyntaxErrorCode::NotSupportedAtPosition, self::UNT);

                    continue;
                }

                $segmentPosition++;
                $this->checkMessageTrailer($segment, $messageCount, $segmentPosition, $unhRef);
                $inMessage = false;

                continue;
            }

            if ($name === self::UNZ) {
                if ($inMessage) {
                    $this->addMessageFailure($messageCount, SyntaxErrorCode::Missing, self::UNT, $unhRef);
                    $inMessage = false;
                }

                $sawUnz = true;
                $this->checkInterchangeTrailer($segment, $messageCount, $unbRef);

                continue;
            }

            if (!$inMessage) {
                $this->addSegmentOutsideMessageFailure($name);

                continue;
            }

            $segmentPosition++;
            $this->validateSegmentElements($segment, $messageCount, $segmentPosition, $unhRef);
        }

        if ($inMessage) {
            $this->addMessageFailure($messageCount, SyntaxErrorCode::Missing, self::UNT, $unhRef);
        }

        if (!$sawAnySegment) {
            $this->addInterchangeFailure(SyntaxErrorCode::Missing, self::UNB);
        }

        if (!$sawUnz) {
            $this->addInterchangeFailure(SyntaxErrorCode::Missing, self::UNZ);
        }

        if ($sawUnb && $sawUnz && $messageCount === 0 && $this->interchangeFailures === []) {
            $this->addInterchangeFailure(SyntaxErrorCode::LowerLevelEmpty);
        }

        return $this->collectFailures($messageCount);
    }

    private function registerMessageReference(null|string $unhRef, int $messageCounter): void
    {
        if ($unhRef === null || $unhRef === '') {
            $this->addMessageFailure($messageCounter, SyntaxErrorCode::Missing, self::UNH, null);

            return;
        }

        if (array_key_exists($unhRef, $this->seenMessageReferences)) {
            $this->addMessageFailure($messageCounter, SyntaxErrorCode::DuplicateDetected, self::UNH, $unhRef, $unhRef);

            return;
        }

        $this->seenMessageReferences[$unhRef] = true;
    }

    private function checkMessageTrailer(
        Segment $unt,
        int $messageCounter,
        int $segmentPosition,
        null|string $unhRef,
    ): void {
        $this->checkMessageTrailerCount($unt->getValueFromPosition(1, 0), $messageCounter, $segmentPosition, $unhRef);
        $this->checkMessageTrailerReference($unt->getValueFromPosition(2, 0), $messageCounter, $unhRef);
    }

    private function checkMessageTrailerCount(
        null|string $untCount,
        int $messageCounter,
        int $segmentPosition,
        null|string $unhRef,
    ): void {
        if ($untCount === null || $untCount === '') {
            $this->addMessageFailure($messageCounter, SyntaxErrorCode::Missing, self::UNT, $unhRef);

            return;
        }

        if ((int) $untCount !== $segmentPosition) {
            $this->addMessageFailure(
                $messageCounter,
                SyntaxErrorCode::ControlCountDoesNotMatch,
                self::UNT,
                $unhRef,
                $untCount,
            );
        }
    }

    private function checkMessageTrailerReference(null|string $untRef, int $messageCounter, null|string $unhRef): void
    {
        if ($untRef === null || $untRef === '') {
            $this->addMessageFailure($messageCounter, SyntaxErrorCode::Missing, self::UNT, $unhRef);

            return;
        }

        if ($unhRef !== null && $unhRef !== '' && $untRef !== $unhRef) {
            $this->addMessageFailure(
                $messageCounter,
                SyntaxErrorCode::ReferencesDoNotMatch,
                self::UNT,
                $unhRef,
                $untRef,
            );
        }
    }

    private function checkInterchangeTrailer(Segment $unz, int $messageCount, null|string $unbRef): void
    {
        $this->checkInterchangeTrailerCount($unz->getValueFromPosition(1, 0), $messageCount);
        $this->checkInterchangeTrailerReference($unz->getValueFromPosition(2, 0), $unbRef);
    }

    private function checkInterchangeTrailerCount(null|string $unzCount, int $messageCount): void
    {
        if ($unzCount === null || $unzCount === '') {
            $this->addInterchangeFailure(SyntaxErrorCode::Missing, self::UNZ);

            return;
        }

        if ((int) $unzCount !== $messageCount) {
            $this->addInterchangeFailure(SyntaxErrorCode::ControlCountDoesNotMatch, self::UNZ, $unzCount);
        }
    }

    private function checkInterchangeTrailerReference(null|string $unzRef, null|string $unbRef): void
    {
        if ($unzRef === null || $unzRef === '') {
            $this->addInterchangeFailure(SyntaxErrorCode::Missing, self::UNZ);

            return;
        }

        if ($unbRef !== null && $unbRef !== '' && $unzRef !== $unbRef) {
            $this->addInterchangeFailure(SyntaxErrorCode::ReferencesDoNotMatch, self::UNZ, $unzRef);
        }
    }

    private function validateSegmentElements(
        Segment $segment,
        int $messageCounter,
        int $segmentPosition,
        null|string $messageReference,
    ): void {
        if (!$segment instanceof Validateable) {
            return;
        }

        foreach ($segment->validate($this->segmentValidator) as $failure) {
            $this->segmentFailures[$messageCounter][] = new SyntaxFailure(
                level: ReportLevel::DataElement,
                errorCode: SyntaxErrorCode::fromFailureType($failure->type),
                messageCounter: $messageCounter,
                messageReference: $messageReference,
                segmentPosition: $segmentPosition,
                elementPosition: $failure->elementPosition,
                componentPosition: $failure->componentPosition,
                value: $failure->value,
            );
        }
    }

    private function addInterchangeFailure(
        SyntaxErrorCode $errorCode,
        null|string $serviceSegment = null,
        null|string $value = null,
    ): void {
        $this->interchangeFailures[] = new SyntaxFailure(
            level: ReportLevel::Interchange,
            errorCode: $errorCode,
            serviceSegment: $serviceSegment,
            value: $value,
        );
    }

    private function addMessageFailure(
        int $messageCounter,
        SyntaxErrorCode $errorCode,
        string $serviceSegment,
        null|string $messageReference,
        null|string $value = null,
    ): void {
        $this->messageFailures[$messageCounter][] = new SyntaxFailure(
            level: ReportLevel::Message,
            errorCode: $errorCode,
            serviceSegment: $serviceSegment,
            messageCounter: $messageCounter,
            messageReference: $messageReference,
            value: $value,
        );
    }

    private function addSegmentOutsideMessageFailure(string $segmentName): void
    {
        $this->interchangeFailures[] = new SyntaxFailure(
            level: ReportLevel::Segment,
            errorCode: SyntaxErrorCode::NotSupportedAtPosition,
            value: $segmentName,
        );
    }

    /**
     * @return list<SyntaxFailure>
     */
    private function collectFailures(int $messageCount): array
    {
        if ($this->interchangeFailures !== []) {
            return $this->interchangeFailures;
        }

        $failures = [];

        for ($messageCounter = 1; $messageCounter <= $messageCount; $messageCounter++) {
            if (($this->messageFailures[$messageCounter] ?? []) !== []) {
                $failures = [...$failures, ...$this->messageFailures[$messageCounter]];

                continue;
            }

            $failures = [...$failures, ...($this->segmentFailures[$messageCounter] ?? [])];
        }

        return $failures;
    }
}

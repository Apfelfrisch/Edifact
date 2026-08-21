<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

final readonly class Failure
{
    public const string VALUE_NOT_ALPHA = 'valueNotAlpha';
    public const string VALUE_NOT_DIGIT = 'valueNotDigit';
    public const string VALUE_TOO_LONG = 'valueTooLong';
    public const string VALUE_TOO_SHORT = 'valueTooShort';
    public const string VALUE_LENGTH_INVALID = 'valueLengthInvalid';
    public const string UNKNOWN_ELEMENT = 'unknownElement';
    public const string UNKNOWN_COMPONENT = 'unknownComponent';
    public const string MISSING_ELEMENT = 'missingElement';
    public const string MISSING_COMPONENT = 'missingComponent';

    public function __construct(
        public string $type,
        public string $segmentName,
        public int $elementPosition,
        public int $componentPosition,
        public null|string $value,
        public string $message,
        public int $messageCounter = 0,
        public int $unhCounter = 0,
    ) {}

    public function withCounters(int $messageCounter, int $unhCounter): self
    {
        return new self(
            $this->type,
            $this->segmentName,
            $this->elementPosition,
            $this->componentPosition,
            $this->value,
            $this->message,
            $messageCounter,
            $unhCounter,
        );
    }
}

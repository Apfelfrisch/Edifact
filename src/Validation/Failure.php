<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

final class Failure
{
    public const VALUE_NOT_ALPHA = 'valueNotAlpha';
    public const VALUE_NOT_DIGIT = 'valueNotDigit';
    public const VALUE_TOO_LONG = 'valueTooLong';
    public const VALUE_TOO_SHORT = 'valueTooShort';
    public const VALUE_LENGTH_INVALID = 'valueLengthInvalid';
    public const UNKOWN_ELEMENT = 'unkownElement';
    public const UNKOWN_COMPONENT = 'unkownComponent';
    public const MISSING_ELEMENT = 'missingElement';
    public const MISSING_COMPONENT = 'missingComponent';

    public function __construct(
        private string $type,
        private string $segmentName,
        private int $elementPosition,
        private int $componentPosition,
        private ?string $value,
        private string $message,
        private int $messageCounter = 0,
        private int $unhCounter = 0,
    ) {
    }

    public function setMessageCounter(int $messageCounter): self
    {
        $this->messageCounter = $messageCounter;

        return $this;
    }

    public function setUnhCounter(int $unhCounter): self
    {
        $this->unhCounter = $unhCounter;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSegmentName(): string
    {
        return $this->segmentName;
    }

    public function getElementPosition(): int
    {
        return $this->elementPosition;
    }

    public function getComponentPosition(): int
    {
        return $this->componentPosition;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageCounter(): int
    {
        return $this->messageCounter;
    }

    public function getUnhCounter(): int
    {
        return $this->unhCounter;
    }
}

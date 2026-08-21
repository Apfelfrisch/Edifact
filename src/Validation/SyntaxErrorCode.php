<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

/**
 * UN/EDIFACT syntax error codes (service code list 0085) as used in the
 * UCI, UCM, UCS and UCD segments of a CONTRL message.
 */
enum SyntaxErrorCode: int
{
    case SyntaxVersionNotSupported = 2;
    case WrongRecipient = 7;
    case InvalidValue = 12;
    case Missing = 13;
    case NotSupportedAtPosition = 15;
    case TooManyConstituents = 16;
    case InvalidDecimalNotation = 19;
    case InvalidAsServiceCharacter = 20;
    case InvalidCharacters = 21;
    case InvalidServiceCharacters = 22;
    case UnknownSender = 23;
    case TestIndicatorNotSupported = 25;
    case DuplicateDetected = 26;
    case ReferencesDoNotMatch = 28;
    case ControlCountDoesNotMatch = 29;
    case LowerLevelEmpty = 32;
    case TooManySegmentRepetitions = 35;
    case TooManySegmentGroupRepetitions = 36;
    case InvalidCharacterType = 37;
    case MissingDigitBeforeDecimal = 38;
    case DataElementTooLong = 39;
    case DataElementTooShort = 40;

    private const array DESCRIPTIONS = [
        self::SyntaxVersionNotSupported->value => 'Syntax version or level not supported',
        self::WrongRecipient->value => 'Recipient of the interchange is not the actual recipient',
        self::InvalidValue->value => 'Invalid value',
        self::Missing->value => 'Missing',
        self::NotSupportedAtPosition->value => 'Not supported at this position',
        self::TooManyConstituents->value => 'Too many constituents',
        self::InvalidDecimalNotation->value => 'Invalid decimal notation',
        self::InvalidAsServiceCharacter->value => 'Character invalid as service character',
        self::InvalidCharacters->value => 'Invalid character(s)',
        self::InvalidServiceCharacters->value => 'Invalid service character(s)',
        self::UnknownSender->value => 'Unknown sender of the interchange',
        self::TestIndicatorNotSupported->value => 'Test indicator not supported',
        self::DuplicateDetected->value => 'Duplicate detected',
        self::ReferencesDoNotMatch->value => 'References do not match',
        self::ControlCountDoesNotMatch->value => 'Control count does not match number of received instances',
        self::LowerLevelEmpty->value => 'Lower level empty',
        self::TooManySegmentRepetitions->value => 'Too many segment repetitions',
        self::TooManySegmentGroupRepetitions->value => 'Too many segment group repetitions',
        self::InvalidCharacterType->value => 'Invalid type of character(s)',
        self::MissingDigitBeforeDecimal->value => 'Missing digit in front of decimal sign',
        self::DataElementTooLong->value => 'Data element too long',
        self::DataElementTooShort->value => 'Data element too short',
    ];

    /**
     * Map a Failure type of the blueprint based segment validation
     * to its UN/EDIFACT syntax error code.
     */
    public static function fromFailureType(string $failureType): self
    {
        return match ($failureType) {
            Failure::MISSING_ELEMENT, Failure::MISSING_COMPONENT => self::Missing,
            Failure::UNKNOWN_ELEMENT, Failure::UNKNOWN_COMPONENT => self::TooManyConstituents,
            Failure::VALUE_NOT_ALPHA, Failure::VALUE_NOT_DIGIT => self::InvalidCharacterType,
            Failure::VALUE_TOO_LONG => self::DataElementTooLong,
            Failure::VALUE_TOO_SHORT => self::DataElementTooShort,
            default => self::InvalidValue,
        };
    }

    public function description(): string
    {
        return self::DESCRIPTIONS[$this->value];
    }
}

<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\SegmentCountTrait;
use Iterator;
use Laminas\Validator\Digits;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use Throwable;

class Validator
{
    use SegmentCountTrait;

    public const ELEMENT_TYPE_ALPHA = 'a';
    public const ELEMENT_TYPE_NUMERIC = 'n';
    public const ELEMENT_TYPE_ALPHA_NUMERIC = 'an';
    public const ELEMENT_NEEDED = 'm';
    public const ELEMENT_OPTIONAL = 'o';

    private StringLength $stringLengthValidator;
    private ValidatorInterface $digitsValidator;
    private ValidatorInterface $alphaValidator;

    public function __construct(
        StringLength $stringLengthValidator = null,
        ValidatorInterface $digitsValidator = null,
        ValidatorInterface $alphaValidator = null,
    ) {
        $this->stringLengthValidator = $stringLengthValidator ?? new StringLength;
        $this->digitsValidator = $digitsValidator ?? new Digits;
        $this->alphaValidator = $alphaValidator ?? new Regex("/^[A-Za-z]*$/");
    }

    /** @psalm-return Iterator<Failure> */
    public function validate(Message $message): Iterator
    {
        foreach ($message->getSegments() as $segment) {
            $this->countSegments($segment);
            foreach ($segment::blueprint()->toArray() as $elementKey => $components) {
                foreach ($components as $componentKey => $rules) {
                    if ($rules === null) {
                        continue;
                    }

                    $value = $segment->getValue($elementKey, $componentKey);

                    [$necessaryState, $type, $lenght] = $this->explodeRules($rules);

                    if ($necessaryState === self::ELEMENT_OPTIONAL && $value === null) {
                        continue;
                    }

                    if ($type === self::ELEMENT_TYPE_NUMERIC && ! $this->digitsValidator->isValid($value)) {
                        /** @var string $message */
                        foreach ($this->digitsValidator->getMessages() as $message) {
                            yield new Failure($segment->name(), $this->messageCounter, $this->unhCounter, $elementKey, $componentKey, $value, $message);
                        }
                    }

                    if ($type === self::ELEMENT_TYPE_ALPHA && ! $this->alphaValidator->isValid($value)) {
                        /** @var string $message */
                        foreach ($this->alphaValidator->getMessages() as $message) {
                            yield new Failure($segment->name(), $this->messageCounter, $this->unhCounter, $elementKey, $componentKey, $value, $message);
                        }
                    }

                    $this->stringLengthValidator->setMax((int)$lenght);
                    if (! $this->stringLengthValidator->isValid((string)$value)) {
                        /** @var string $message */
                        foreach ($this->stringLengthValidator->getMessages() as $message) {
                            yield new Failure(
                                $segment->name(),
                                $this->messageCounter,
                                $this->unhCounter,
                                $elementKey,
                                $componentKey,
                                $value,
                                $message
                            );
                        }
                    }
                }
            }
        }
    }

    public function validateUntilFirstFailure(Message $message): Failure|null
    {
        foreach ($this->validate($message) as $failure) {
            return $failure;
        }
        return null;
    }

    private function explodeRules(string $rules): array
    {
        try {
            return explode('|', $rules);
        } catch (Throwable) {
            throw new EdifactException("Invalid Validation Rule [$rules]");
        }
    }
}

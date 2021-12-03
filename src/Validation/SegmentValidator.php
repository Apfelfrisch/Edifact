<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Iterator;
use Laminas\Validator\Digits;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use Throwable;

class SegmentValidator implements SegValidatorInterface
{
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
    public function validate(Elements $blueprint, Elements $segment): Iterator
    {
        foreach ($blueprint->toArray() as $elementKey =>  $components) {
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
                        yield new Failure($segment->getName(), $elementKey, $componentKey, $value, $message);
                    }
                }

                if ($type === self::ELEMENT_TYPE_ALPHA && ! $this->alphaValidator->isValid($value)) {
                    /** @var string $message */
                    foreach ($this->alphaValidator->getMessages() as $message) {
                        yield new Failure($segment->getName(), $elementKey, $componentKey, $value, $message);
                    }
                }

                $this->stringLengthValidator->setMax((int)$lenght);
                if (! $this->stringLengthValidator->isValid((string)$value)) {
                    /** @var string $message */
                    foreach ($this->stringLengthValidator->getMessages() as $message) {
                        yield new Failure(
                            $segment->getName(),
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

    private function explodeRules(string $rules): array
    {
        try {
            return explode('|', $rules);
        } catch (Throwable) {
            throw new EdifactException("Invalid Validation Rule [$rules]");
        }
    }
}

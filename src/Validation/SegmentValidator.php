<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\Interfaces\SegValidatorInterface;
use Iterator;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use Throwable;

class SegmentValidator implements SegValidatorInterface
{
    public const ELEMENT_TYPE_ALPHA = 'a';
    public const ELEMENT_TYPE_NUMERIC = 'n';
    public const ELEMENT_TYPE_ALPHA_NUMERIC = 'an';
    public const ELEMENT_NEEDED = 'M';
    public const ELEMENT_OPTIONAL = 'O';


    public function __construct(
        private StringLength $stringLengthValidator,
        private ValidatorInterface $digitsValidator,
        private ValidatorInterface $alphaValidator,
    ) { }

    /** @psalm-return Iterator<Failure> */
    public function validate(Elements $blueprint, Elements $data): Iterator
    {
        foreach ($blueprint->toArray() as $elementKey => $components) {
            foreach ($components as $componentKey => $rules) {
                if ($rules === null) {
                    continue;
                }

                $value = $data->getValue($elementKey, $componentKey);

                [$necessaryState, $type, $lenght] = $this->explodeRules($rules);

                if ($necessaryState === self::ELEMENT_OPTIONAL && $value === null) {
                    continue;
                }

                if ($type === self::ELEMENT_TYPE_NUMERIC && ! $this->digitsValidator->isValid($value)) {
                    /** @var string $message */
                    foreach ($this->digitsValidator->getMessages() as $message) {
                        yield new Failure($data->getName(), $elementKey, $componentKey, $value, $message);
                    }
                }

                if ($type === self::ELEMENT_TYPE_ALPHA && ! $this->alphaValidator->isValid($value)) {
                    /** @var string $message */
                    foreach ($this->alphaValidator->getMessages() as $message) {
                        yield new Failure($data->getName(), $elementKey, $componentKey, $value, $message);
                    }
                }

                $this->stringLengthValidator->setMax((int)$lenght);
                if (! $this->stringLengthValidator->isValid((string)$value)) {
                    /** @var string $message */
                    foreach ($this->stringLengthValidator->getMessages() as $message) {
                        yield new Failure($data->getName(), $elementKey, $componentKey, $value, $message);
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

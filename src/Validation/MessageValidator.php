<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Interfaces\MessageInterface;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

/*
 * Todo: Klasse komplett neuschreiben, die ist Mist
 */
class MessageValidator implements MessageValidatorInterface
{
    private $lineCount = 0;
    private $blueprintValidator;

    public function validate(MessageInterface $edifact)
    {
        $this->blueprintValidator = new Blueprint($edifact->getValidationBlueprint());
        try {
            $this->loop($edifact);
        } catch (EdifactException $e) {
            throw new ValidationException($e->getMessage(), $this->lineCount, null);
        }

        return $this;
    }

    public function loop($edifact)
    {
        while ($line = $edifact->getNextSegment()) {
            $this->lineCount++;
            $this->blueprintValidator->validate($line);
            $this->validateSegment($line);
        }
    }

    private function validateSegment(SegInterface $segment)
    {
        try {
            $segment->validate();
        } catch (SegValidationException $e) {
            throw new ValidationException($e->getMessage(), null, @$segment->name());
        }
    }
}

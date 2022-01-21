<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Exceptions;

use Apfelfrisch\Edifact\Validation\Validator;

class ValidationException extends EdifactException
{
    public static function segmentNotValidateable(string $segmentClass): self
    {
        return new self("[" . $segmentClass . "] not validateable.");
    }

    public static function messageNotValidated(): self
    {
        return new self("No Message was validated, call [" . Validator::class . "::isValid] first.");
    }

    public static function invalidRule(string $rule): self
    {
        return new self("Invalid Validation Rule [$rule].");
    }
}

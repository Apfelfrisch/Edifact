<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Exceptions;

class InvalidStreamException extends EdifactException
{
    public static function readError(string $filename): self
    {
        throw new self("Failed to open stream: [{$filename}]");
    }

    public static function filterError(string $filtername): self
    {
        return new self("Unable to locate filter: ['$filtername']");
    }
}

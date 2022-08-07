<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Exceptions;

class BuildException extends EdifactException
{
    public static function finalizedBuildNotModifiable(): self
    {
        return new self("Cannot add Segments to finalized Builder.");
    }
}

<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

interface MessageFactory
{
    public function fromString(string $string): Message;

    public function fromFilepath(string $filepath): Message;
}

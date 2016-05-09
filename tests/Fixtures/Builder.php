<?php

namespace Proengeno\Edifact\Test\Fixtures;

use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\Builder as BuilderCore;

class Builder extends BuilderCore
{
    protected $messageType = 'RANDOM_MESSAGE';

    public function addMessage()
    {
        return null;
    }
}

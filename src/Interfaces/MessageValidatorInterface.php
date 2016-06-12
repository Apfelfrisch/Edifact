<?php 

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Interfaces\MessageInterface;

interface MessageValidatorInterface {
    public function validate(MessageInterface $edifact);
} 

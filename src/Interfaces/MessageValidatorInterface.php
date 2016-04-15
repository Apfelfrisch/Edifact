<?php 

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Interfaces\EdifactMessageInterface;

interface MessageValidatorInterface {
    public function validate(EdifactMessageInterface $edifact);
} 

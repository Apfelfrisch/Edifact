<?php 

namespace Proengeno\Edifact\Interfaces;

interface SegValidatorInterface {
    public function validate($blueprint, $data);
} 

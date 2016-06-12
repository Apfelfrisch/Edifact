<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Exceptions\EdifactException;

class Edifact
{
    protected $builder;
    protected $resolver;
    
    public function __construct($builder, $resolver)
    {
        $this->builder = $builder;
        $this->resolver = $resolver;
    }

    public function build($key, $to)
    {
        return $this->builder->build($key, $to);
    }
    
    public function resolveFromFile($filepath)
    {
        return $this->resolver->fromFile($filepath);
    }

    public function resolveFromString($string)
    {
        return $this->resolver->fromString($string);
    }
}

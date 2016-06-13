<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\EdifactBuilder;
use Proengeno\Edifact\EdifactResolver;

class Edifact
{
    protected $builder;
    protected $resolver;
    
    public function __construct(EdifactBuilder $builder, EdifactResolver $resolver)
    {
        $this->builder = $builder;
        $this->resolver = $resolver;
    }

    public function build($key, $to, $filename = null)
    {
        return $this->builder->build($key, $to, $filename);
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

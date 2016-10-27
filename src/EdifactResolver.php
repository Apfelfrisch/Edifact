<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactResolver
{
    private $edifactFile;
    private $configuration;

    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration;
    }

    public function fromFile($filepath)
    {
        $this->edifactFile = new EdifactFile($filepath);

        return new Message($this->resolvEdifactObject());
    }

    public function fromString($string)
    {
        $this->edifactFile = new EdifactFile('php://temp', 'w+');
        $this->edifactFile->writeAndRewind($string);

        return new Message($this->resolvEdifactObject());
    }

    // Need Refactoring
    private function resolvEdifactObject()
    {
        $tmpAllocationRules = $this->configuration->getAllImportAllocationRules();

        while ($segment = $this->edifactFile->getSegment()) {
            $segmenName = substr($segment, 0, 3);

            foreach ($tmpAllocationRules as $edifactClass => $rules) {
                if (isset($rules[$segmenName])) {
                    if (preg_match($rules[$segmenName], $segment)) {
                        unset($tmpAllocationRules[$edifactClass][$segmenName]);
                        if (count($tmpAllocationRules[$edifactClass]) == 0) {
                            return new $edifactClass($this->edifactFile, $this->configuration);
                        }
                    }
                }
            }
        }
        throw new EdifactException('Could find Message, for given rules');
    }
}

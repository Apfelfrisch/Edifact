<?php

namespace Proengeno\Edifact\Templates;

use Closure;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Message\SegmentFactory;
use Proengeno\Edifact\Exceptions\EdifactException;

abstract class AbstractBuilder
{
    protected $edifactClass = null;

    protected $to;
    protected $from;
    protected $edifactFile;
    protected $configuration;
    protected $buildCache = [];

    private $unhCounter = 0;
    private $messageCount = 0;
    private $messageWasFetched = false;

    public function __construct($to, $filename = null, Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration;

        $this->to = $to;
        $this->from = $this->configuration->getExportSender();
        $this->edifactFile = new EdifactFile($this->getFullpath($filename), 'w+');
    }

    public function __destruct()
    {
        // Delete File if build process could not finshed (Expetion, etc)
        if ($this->edifactFile) {
            $filepath = $this->edifactFile->getRealPath();
            if ($this->messageWasFetched === false && file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    public function addMessage($message)
    {
        if ($this->messageIsEmpty()) {
            $this->writeSeg('una');
            $this->writeUnb();
        }
        $this->writeMessage($message);
        $this->messageCount++;
    }

    public function unbReference()
    {
        if (!isset($this->buildCache['unbReference'])) {
            $generateUnbRef = $this->configuration->getUnbRefGenerator();
            $this->buildCache['unbReference'] = $generateUnbRef();
        }

        return $this->buildCache['unbReference'];
    }

    public function getSegmentFactory()
    {
        if (!isset($this->buildCache['segmentFactory'])) {
            $this->buildCache['segmentFactory'] = new SegmentFactory(
                $this->configuration->getSegmentNamespace(),
                $this->configuration->getDelimiter()
            );
        }

        return $this->buildCache['segmentFactory'];
    }

    public function unhCount()
    {
        return $this->unhCounter;
    }

    public function messageCount()
    {
        return $this->messageCount;
    }

    public function getOrFail()
    {
        $message = $this->get();
        $this->messageWasFetched = false;
        $message->validate();
        $this->messageWasFetched = true;

        return $message;
    }

    public function get()
    {
        if (!$this->messageIsEmpty()) {
            $this->writeSeg('unz', [$this->messageCount, $this->unbReference()]);
            $this->edifactFile->rewind();
        }

        $edifactObject = new $this->edifactClass($this->edifactFile, $this->configuration);

        $this->messageWasFetched = true;

        return new Message($edifactObject);
    }

    abstract protected function writeUnb();

    abstract protected function writeMessage($array);

    protected function writeSeg($segmentName, $attributes = [], $method = 'fromAttributes')
    {
        $segment = $this->getSegmentFactory()->fromAttributes($segmentName, $attributes, $method);
        $this->edifactFile->write($segment);
        $this->countSegments($segment);
    }

    private function messageIsEmpty()
    {
        return $this->edifactFile->tell() == 0;
    }

    private function countSegments($segment)
    {
        if ($segment->name() == 'UNA' || $segment->name() == 'UNB') {
            return;
        }
        if ($segment->name() == 'UNH') {
            $this->unhCounter = 1;
            return;
        }
        $this->unhCounter++;
    }

    private function getFullpath($filename)
    {
        if ($filename === null) {
            return $this->configuration->getFilename();
        }
        if ($this->configuration->getFilepath() === null) {
            return $filename;
        }
        return $this->configuration->getFilepath() . '/' . $filename;
    }
}


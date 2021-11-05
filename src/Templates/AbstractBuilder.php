<?php

namespace Proengeno\Edifact\Templates;

use Closure;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Interfaces\BuilderInterface;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Describer;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Message\SegmentFactory;
use Proengeno\Edifact\Exceptions\EdifactException;

abstract class AbstractBuilder implements BuilderInterface
{
    /** @var string */
    protected $to;

    /** @var string */
    protected $from;

    /** @var EdifactFile */
    protected $edifactFile;

    /** @var Describer */
    protected $description;

    /** @var Configuration */
    protected $configuration;

    /** @var array */
    protected $buildCache = [];

    /** @var int */
    private $unhCounter = 0;

    /** @var int */
    private $messageCount = 0;

    /** @var bool */
    private $messageWasFetched = false;

    /**
     * @param string $to
     * @param Configuration $configuration
     * @param string $filename
     */
    public function __construct($to, Configuration $configuration, $filename = 'php://temp')
    {
        $this->configuration = $configuration;
        $this->to = $to;
        $this->from = $this->configuration->getExportSender();
        $this->description = Describer::build($this->getDescriptionPath());
        $this->edifactFile = new EdifactFile($this->getFullpath($filename), 'w+', $this->configuration->getDelimiter());
        foreach ($this->configuration->getWriteFilter() as $callable) {
            $this->edifactFile->addWriteFilter($callable);
        }
    }

    public function __destruct()
    {
        // Delete File if build process could not finshed (Expetion, etc)
        $filepath = $this->edifactFile->getRealPath();
        if ($this->messageWasFetched === false && file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * @no-named-arguments
     *
     * @param iterable $message
     *
     * @return void
     */
    public function addMessage($message)
    {
        if ($this->messageIsEmpty()) {
            $delimiter = $this->edifactFile->getDelimiter();

            $this->writeSeg('una', [
                'data' => $delimiter->getData(),
                'dataGroup' => $delimiter->getDataGroup(),
                'decimal' => $delimiter->getDecimal(),
                'terminator' => $delimiter->getTerminator(),
                'empty' => $delimiter->getEmpty(),
            ]);

            $this->writeUnb();
        }
        $this->writeMessage($message);
        $this->messageCount++;
    }

    /**
     * @return string
     */
    public function unbReference()
    {
        if (!isset($this->buildCache['unbReference'])) {
            $generateUnbRef = $this->configuration->getUnbRefGenerator();
            $this->buildCache['unbReference'] = (string)$generateUnbRef();
        }

        return $this->buildCache['unbReference'];
    }

    /**
     * @return SegmentFactory
     */
    public function getSegmentFactory()
    {
        if (!isset($this->buildCache['segmentFactory'])) {
            $this->buildCache['segmentFactory'] = new SegmentFactory(
                $this->configuration->getSegmentNamespace(),
                $this->edifactFile->getDelimiter(),
            );
        }

        return $this->buildCache['segmentFactory'];
    }

    /**
     * @return int
     */
    public function unhCount()
    {
        return $this->unhCounter;
    }

    /**
     * @return int
     */
    public function messageCount()
    {
        return $this->messageCount;
    }

    /**
     * @return Message
     */
    public function getOrFail()
    {
        $message = $this->get();
        $this->messageWasFetched = false;
        $message->validate();
        $this->messageWasFetched = true;

        return $message;
    }

    /**
     * @return Message
     */
    public function get()
    {
        $this->finalize();

        return new Message($this->edifactFile, $this->configuration, $this->description);
    }

    /**
     * @return string
     */
    abstract protected function getDescriptionPath();

    /**
     * @return void
     */
    abstract protected function writeUnb();

    /**
     * @no-named-arguments
     *
     * @param iterable $message
     *
     * @return void
     */
    abstract protected function writeMessage($message);

    /**
     * @return void
     */
    protected function finalize()
    {
        if (!$this->messageIsEmpty()) {
            $this->writeSeg('unz', [$this->messageCount, $this->unbReference()]);
            $this->edifactFile->rewind();
        }

        $this->messageWasFetched = true;
    }

    /**
     * @param string $segmentName
     * @param array $attributes
     * @param string $method
     *
     * @return void
     */
    protected function writeSeg($segmentName, $attributes = [], $method = 'fromAttributes')
    {
        $segment = $this->getSegmentFactory()->fromAttributes($segmentName, $attributes, $method);
        $this->edifactFile->write((string)$segment);
        $this->countSegments($segment);
    }

    /**
     * @return bool
     */
    private function messageIsEmpty()
    {
        return $this->edifactFile->tell() === 0;
    }

    /**
     * @param SegInterface $segment
     *
     * @return void
     */
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

    /**
     * @param string $filename
     *
     * @return string
     */
    private function getFullpath($filename)
    {
        if (substr($filename, 0, 4) === 'php:') {
            return $filename;
        }
        if ($this->configuration->getFilepath() === null) {
            return $filename;
        }
        return $this->configuration->getFilepath() . '/' . $filename;
    }
}


<?php

namespace Proengeno\Edifact\Templates;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Interfaces\BuilderInterface;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\Describer;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Message\SegmentFactory;

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

    private SegmentFactory $segmentFactory;

    private string $unbReference;

    private int $unhCounter = 0;

    private int $messageCount = 0;

    private bool $messageWasFetched = false;

    /**
     * @param string $to
     * @param Configuration $configuration
     * @param string $filename
     */
    public function __construct($to, Configuration $configuration, $filename = 'php://temp')
    {
        $this->to = $to;

        $this->configuration = $configuration;

        $this->from = $this->configuration->getExportSender();

        $this->description = Describer::build($this->getDescriptionPath());

        $this->unbReference = (string)$this->configuration->getUnbRefGenerator()();

        $this->edifactFile = new EdifactFile($this->getFullpath($filename), 'w+', $this->configuration->getDelimiter());

        $this->segmentFactory = new SegmentFactory($this->configuration->getSegmentNamespace(), $this->edifactFile->getDelimiter());

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
        return $this->unbReference;
    }

    /**
     * @return SegmentFactory
     */
    public function getSegmentFactory()
    {
        return $this->segmentFactory;
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
        if (! $this->messageIsEmpty()) {
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

        $this->edifactFile->write($segment->toString() . $this->edifactFile->getDelimiter()->getSegment());
        $this->countSegments($segment);
    }

    private function messageIsEmpty(): bool
    {
        return $this->edifactFile->tell() === 0;
    }

    private function countSegments(SegInterface $segment): void
    {
        if ($segment->name() == 'UNA' || $segment->name() == 'UNB') {
            return;
        }
        if ($segment->name() === 'UNH') {
            $this->unhCounter = 1;
            return;
        }
        $this->unhCounter++;
    }

    private function getFullpath(string $filename): string
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


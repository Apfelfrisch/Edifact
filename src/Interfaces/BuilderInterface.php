<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message;
use Proengeno\Edifact\SegmentFactory;

interface BuilderInterface
{
    /**
     * @param string $to
     * @param Configuration $configuration
     * @param string $filename
     */
    public function __construct($to, Configuration $configuration, $filename);

    /**
     * @no-named-arguments
     *
     * @param iterable $message
     *
     * @return void
     */
    public function addMessage($message);

    /**
     * @return string
     */
    public function unbReference();

    /**
     * @return SegmentFactory
     */
    public function getSegmentFactory();

    /**
     * @return int
     */
    public function unhCount();

    /**
     * @return int
     */
    public function messageCount();

    /**
     * @return Message
     */
    public function getOrFail();

    /**
     * @return Message
     */
    public function get();
}

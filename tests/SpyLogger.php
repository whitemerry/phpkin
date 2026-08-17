<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Logger\Logger;

/**
 * Class SpyLogger
 * Captures what Tracer hands to the logger
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class SpyLogger implements Logger
{
    /**
     * @var array|null
     */
    protected $spans;

    /**
     * SpyLogger constructor.
     *
     * @param $options array
     */
    public function __construct($options = array())
    {
    }

    /**
     * @param $spans array
     */
    public function trace($spans)
    {
        $this->spans = $spans;
    }

    /**
     * Spans received from Tracer
     *
     * @return array|null
     */
    public function getTracedSpans()
    {
        return $this->spans;
    }
}

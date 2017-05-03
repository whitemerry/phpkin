<?php
namespace whitemerry\phpkin;

use whitemerry\phpkin\Identifier\Identifier;
use whitemerry\phpkin\Logger\Logger;
use whitemerry\phpkin\Sampler\Sampler;

/**
 * Class Tracer
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin
 */
class Tracer
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var int
     */
    protected $startTimestamp;

    /**
     * @var Span[]
     */
    protected $spans = [];

    /**
     * Tracer constructor.
     * 
     * @param $name string Name of trace
     * @param $endpoint Endpoint Current application info
     * @param $logger Logger Trace save handler
     * @param $sampler Sampler Calculates 'Sampled' - default DefaultSampler
     * @param $traceId Identifier TraceId - default TraceIdentifier
     * @param $traceSpanId Identifier TraceSpanId/ParentSpanId/ParentId - default SpandIdentifier
     */
    public function __construct($name, $endpoint, $logger, $sampler = null, $traceId = null, $traceSpanId = null)
    {
        TracerInfo::init($sampler, $traceId, $traceSpanId);

        $this->setName($name);
        $this->setEndpoint($endpoint);
        $this->setLogger($logger);

        $this->startTimestamp = zipkin_timestamp();
    }

    /**
     * Adds Span to trace
     *
     * @param $span Span
     */
    public function addSpan($span)
    {
        if (!TracerInfo::isSampled()) {
            return;
        }

        $this->spans[] = $span->toArray();
    }

    /**
     * Save trace
     */
    public function trace()
    {
        $this->addTraceSpan();
        $this->logger->trace($this->spans);
    }

    /**
     * Adds main span to Spans
     */
    protected function addTraceSpan()
    {
        $span = new Span(
            TracerInfo::getTraceSpanId(),
            $this->name,
            new AnnotationBlock(
                AnnotationBlock::TYPE_SERVER,
                $this->endpoint,
                $this->startTimestamp,
                zipkin_timestamp()
            )
        );
        $span->unsetParentId();
    }

    /**
     * Valid and set name
     *
     * @param $name string
     *
     * @throws \InvalidArgumentException
     */
    protected function setName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('$name must be a string');
        }

        $this->name = $name;
    }

    /**
     * Valid and set endpoint
     *
     * @param $endpoint Endpoint
     *
     * @throws \InvalidArgumentException
     */
    protected function setEndpoint($endpoint)
    {
        if (!($endpoint instanceof Endpoint)) {
            throw new \InvalidArgumentException('$endpoint must be instance of Endpoint');
        }

        $this->endpoint = $endpoint;
    }

    /**
     * Valid and set logger
     *
     * @param $logger Logger
     *
     * @throws \InvalidArgumentException
     */
    protected function setLogger($logger)
    {
        if (!($logger instanceof Logger)) {
            throw new \InvalidArgumentException('$logger must be instance of Logger');
        }

        $this->logger = $logger;
    }
}

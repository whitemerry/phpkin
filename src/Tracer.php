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
    const FRONTEND = 'frontend';
    const BACKEND = 'backend';

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
    protected $spans = array();

    /**
     * @var string
     */
    protected $profile = Tracer::FRONTEND;

    /**
     * @var Identifier|null
     */
    protected $parentSpanId;

    /**
     * Tracer constructor.
     *
     * @param $name string Name of trace
     * @param $endpoint Endpoint Current application info
     * @param $logger Logger Trace save handler
     * @param $sampler bool|Sampler Set or calculate 'Sampled' - default true
     * @param $traceId Identifier TraceId - X-B3-TraceId - default TraceIdentifier
     * @param $traceSpanId Identifier SpanId of this request - X-B3-SpanId - default SpanIdentifier
     * @param $parentSpanId Identifier ParentSpanId of the caller - X-B3-ParentSpanId,
     *                      null when this application starts the trace
     */
    public function __construct(
        $name,
        $endpoint,
        $logger,
        $sampler = null,
        $traceId = null,
        $traceSpanId = null,
        $parentSpanId = null
    )
    {
        TracerInfo::init($sampler, $traceId, $traceSpanId);

        $this->setName($name);
        $this->setEndpoint($endpoint);
        $this->setLogger($logger);

        $this->startTimestamp = zipkin_timestamp();

        $this->parentSpanId = $parentSpanId;
    }

    /**
     * Set's application profile
     *
     * @param $profile string Tracer::FRONTEND or Tracer::BACKEND
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * Adds Span to trace
     *
     * The Span is kept as given and serialized by trace(), so anything changed
     * on it afterwards - including a Metadata instance it shares with another
     * Span - ends up in the trace.
     *
     * @param $span Span
     *
     * @throws \InvalidArgumentException
     */
    public function addSpan($span)
    {
        if (!($span instanceof Span)) {
            throw new \InvalidArgumentException('$span must be instance of Span');
        }

        if (!TracerInfo::isSampled()) {
            return;
        }

        $this->spans[] = $span;
    }

    /**
     * Spans collected so far
     *
     * The span describing the trace itself is built by trace(), because its
     * end timestamp is not known before that. Call this after trace() to get
     * the complete set. Empty when the trace is not sampled.
     *
     * @return Span[]
     */
    public function getSpans()
    {
        return $this->spans;
    }

    /**
     * Save trace
     */
    public function trace()
    {
        if (!TracerInfo::isSampled()) {
            return;
        }

        $this->addTraceSpan();
        $this->logger->trace($this->spansToArray());
    }

    /**
     * Converts Spans to arrays
     *
     * @return array
     */
    protected function spansToArray()
    {
        $spans = array();
        foreach ($this->spans as $span) {
            $spans[] = $span->toArray();
        }

        return $spans;
    }

    /**
     * Adds main span to Spans
     *
     * The span is parented to the caller's span when one was propagated through
     * X-B3-ParentSpanId, otherwise this application starts the trace and the span
     * is a root. Span defaults ParentId to the current SpanId, which would make the
     * span its own parent, so it is always set explicitly here.
     */
    protected function addTraceSpan()
    {
        $span = new Span(
            TracerInfo::getTraceSpanId(),
            $this->name,
            new AnnotationBlock(
                $this->endpoint,
                $this->startTimestamp,
                zipkin_timestamp(),
                AnnotationBlock::SERVER
            ),
            null,
            null,
            $this->parentSpanId
        );

        if ($this->parentSpanId === null) {
            $span->unsetParentId();
        }

        $this->addSpan($span);
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

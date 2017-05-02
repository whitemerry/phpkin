<?php
namespace whitemerry\phpkin;

use whitemerry\phpkin\identifier\SpanIdentifier;
use whitemerry\phpkin\identifier\TraceIdentifier;
use whitemerry\phpkin\Sampler\DefaultSampler;
use whitemerry\phpkin\Sampler\Sampler;

class Zipkin
{
    /**
     * @var bool
     */
    protected static $sampled;
    /**
     * @var Logger
     */
    protected static $logger;

    /**
     * @var Identifier
     */
    protected static $traceId;

    /**
     * @var Identifier
     */
    protected static $traceSpanId;

    /**
     * @var int
     */
    protected static $serverRecive;

    /**
     * @var string
     */
    protected static $name;

    /**
     * @var Endpoint
     */
    protected static $endpoint;

    /**
     * @var Span[]
     */
    protected static $spans = [];

    /**
     * Zipkin initialize.
     *
     * @param $logger Logger Logger engine used to store/send logs
     * @param $name string Trace name
     * @param $endpoint Endpoint Current request data
     * @param $traceId Identifier Trace identifier
     * @param $traceSpanId Identifier Span trace identifier (used to fill parentSpanId)
     * @param $sampler Sampler Sampler used to toggle Zipkin logs
     */
    public static function init($logger, $name, $endpoint, $traceId = null, $traceSpanId = null, $sampler = null)
    {
        static::setLogger($logger);
        static::setName($name);
        static::setEndpoint($endpoint);
        static::setSampled($sampler, DefaultSampler::class);
        static::setIdentifier('traceId', $traceId, TraceIdentifier::class);
        static::setIdentifier('traceSpanId', $traceSpanId, SpanIdentifier::class);

        if (static::$sampled) {
            static::$serverRecive = static::getTimestamp();
        }
    }

    /**
     * Current TraceId for X-B3-TraceId header
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * @return Identifier
     */
    public static function getTraceId()
    {
        return self::$traceId;
    }

    /**
     * Current ParentSpanId for X-B3-ParentSpanId
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * @return Identifier
     */
    public static function getTraceSpanId()
    {
        return self::$traceSpanId;
    }

    /**
     * Current Sampled for X-B3-Sampled
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * @return bool
     */
    public static function isSampled()
    {
        return self::$sampled;
    }

    /**
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information#timestamps-and-duration
     *
     * @return int Current Unix timestamp in microseconds
     */
    public static function getTimestamp()
    {
        return intval(microtime(true) * 1000 * 1000);
    }

    /**
     * Adds Span to trace
     *
     * @param $span Span
     */
    public static function addSpan($span)
    {
        if (!static::$sampled) {
            return;
        }

        static::$spans[] = $span->toArray();
    }

    /**
     * Generate trace and global span, then send to Logger
     */
    public static function trace()
    {
        if (!static::$sampled) {
            return;
        }
        static::addSpan(
            new Span(
                static::$name,
                static::$serverRecive,
                static::getTimestamp(),
                static::$endpoint,
                static::$traceSpanId,
                Span::AUTO_IDENTIFIER,
                Span::EMPTY_IDENTIFIER
            )
        );

        static::$logger->trace(static::$spans);
    }

    /**
     * Valid and set sampler
     *
     * @param $sampler Sampler|null
     * @param $defaultSampler callable
     *
     * @throws \InvalidArgumentException
     */
    protected static function setSampled($sampler, $defaultSampler)
    {
        if ($sampler === null) {
            $sampler = new $defaultSampler();
        }

        if (!($sampler instanceof Sampler)) {
            throw new \InvalidArgumentException('$sampler must be instance of Sampler');
        }

        static::$sampled = $sampler->isSampled();
    }

    /**
     * Valid and set logger
     *
     * @param $logger Logger
     *
     * @throws \InvalidArgumentException
     */
    protected static function setLogger($logger)
    {
        if (!($logger instanceof Logger)) {
            throw new \InvalidArgumentException('$logger must be instance of Logger');
        }

        static::$logger = $logger;
    }

    /**
     * Valid and set name
     *
     * @param $name string
     *
     * @throws \InvalidArgumentException
     */
    protected static function setName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('$name must be a string');
        }

        static::$name = $name;
    }

    /**
     * Valid and set identifier
     *
     * @param $field string
     * @param $identifier Identifier|null
     * @param $defaultIdentifier callable
     *
     * @throws \InvalidArgumentException
     */
    protected static function setIdentifier($field, $identifier, $defaultIdentifier)
    {
        if ($identifier === null) {
            $identifier = new $defaultIdentifier();
        }

        if (!($identifier instanceof Identifier)) {
            throw new \InvalidArgumentException('$identifier must be instance of Identifier1');
        }

        self::${$field} = $identifier;
    }

    /**
     * Valid and set endpoint
     *
     * @param $endpoint Endpoint
     *
     * @throws \InvalidArgumentException
     */
    protected static function setEndpoint($endpoint)
    {
        if (!($endpoint instanceof Endpoint)) {
            throw new \InvalidArgumentException('$endpoint must be instance of Endpoint');
        }

        static::$endpoint = $endpoint;
    }
}

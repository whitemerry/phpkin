<?php
namespace whitemerry\phpkin;

use whitemerry\phpkin\Identifier\Identifier;
use whitemerry\phpkin\identifier\SpanIdentifier;
use whitemerry\phpkin\identifier\TraceIdentifier;
use whitemerry\phpkin\Sampler\DefaultSampler;
use whitemerry\phpkin\Sampler\Sampler;

/**
 * Class TracerInfo
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin
 */
class TracerInfo
{
    /**
     * @var boolean
     */
    protected static $sampled;

    /**
     * @var Identifier
     */
    protected static $traceId;

    /**
     * @var Identifier
     */
    protected static $traceSpanId;

    /**
     * @param $sampler Sampler Calculates 'Sampled' - default DefaultSampler
     * @param $traceId Identifier TraceId - default TraceIdentifier
     * @param $traceSpanId Identifier TraceSpanId/ParentSpanId/ParentId - default SpandIdentifier
     */
    public static function init($sampler, $traceId, $traceSpanId)
    {
        static::setSampled($sampler, DefaultSampler::class);
        static::setIdentifier('traceId', $traceId, TraceIdentifier::class);
        static::setIdentifier('traceSpanId', $traceSpanId, SpanIdentifier::class);
    }

    /**
     * Current Sampled for X-B3-Sampled
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * @return bool
     */
    public static function isSampled()
    {
        return static::$sampled;
    }

    /**
     * Current TraceId for X-B3-TraceId
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * @return Identifier
     */
    public static function getTraceId()
    {
        return static::$traceId;
    }

    /**
     * Current ParentSpanId/ParentId for X-B3-ParentSpanId
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * @return Identifier
     */
    public static function getTraceSpanId()
    {
        return static::$traceSpanId;
    }

    /**
     * Valid and set sampled
     *
     * @param $sampler Sampler
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
            throw new \InvalidArgumentException('$identifier must be instance of Identifier');
        }

        static::${$field} = $identifier;
    }
}
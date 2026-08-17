<?php
namespace whitemerry\phpkin;

use whitemerry\phpkin\Identifier\Identifier;
use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Identifier\TraceIdentifier;
use whitemerry\phpkin\Sampler\Sampler;

/**
 * Class TracerInfo
 * Contains B3 Propagation data
 * TODO: Debug B3 header
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
     * @param $sampler bool|Sampler Set or calucate 'Sampled' - default true
     * @param $traceId Identifier TraceId - X-B3-TraceId - default TraceIdentifier
     * @param $traceSpanId Identifier SpanId of this request - X-B3-SpanId - default SpanIdentifier
     */
    public static function init($sampler, $traceId, $traceSpanId)
    {
        static::setSampled($sampler);
        static::setIdentifier('traceId', $traceId, 'whitemerry\\phpkin\\Identifier\\TraceIdentifier');
        static::setIdentifier('traceSpanId', $traceSpanId, 'whitemerry\\phpkin\\Identifier\\SpanIdentifier');
    }

    /**
     * Current Sampled for X-B3-Sampled
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * @return bool
     */
    public static function isSampled()
    {
        static::checkInit();
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
        static::checkInit();
        return static::$traceId;
    }

    /**
     * Current SpanId for X-B3-SpanId
     * http://zipkin.io/pages/instrumenting.html#communicating-trace-information
     *
     * This is the id of this request's own span. When calling another service it
     * becomes that service's X-B3-ParentSpanId.
     *
     * @return Identifier
     */
    public static function getTraceSpanId()
    {
        static::checkInit();
        return static::$traceSpanId;
    }

    /**
     * Valid and set sampled
     *
     * @param $sampler Sampler|bool
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    protected static function setSampled($sampler)
    {
        if ($sampler === null) {
            static::$sampled = true;
            return null;
        }

        if (is_bool($sampler)) {
            static::$sampled = $sampler;
            return null;
        }

        if (!is_bool($sampler) && !($sampler instanceof Sampler)) {
            throw new \InvalidArgumentException('$sampler must be instance of Sampler or boolean');
        }

        static::$sampled = $sampler->isSampled();
        return null;
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

    /**
     * Check is initialized
     *
     * @throws \BadMethodCallException
     */
    protected static function checkInit()
    {
        if (empty(static::$traceId)) {
            throw new \BadMethodCallException('TracerInfo must be initialized first');
        }
    }
}

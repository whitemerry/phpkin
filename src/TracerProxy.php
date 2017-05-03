<?php
namespace whitemerry\phpkin;

/**
 * Class TracerProxy
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin
 */
class TracerProxy
{
    /**
     * @var Tracer
     */
    protected static $instance;

    /**
     * Set instance for proxy
     *
     * @param $instance Tracer
     *
     * @throws \InvalidArgumentException
     */
    public static function init($instance)
    {
        static::setInstance($instance);
    }

    /**
     * @see Tracer::addSpan($span)
     */
    public static function addSpan($span)
    {
        static::checkInstance();
        static::$instance->addSpan($span);
    }

    /**
     * @see Tracer::trace()
     */
    public static function trace()
    {
        static::checkInstance();
        static::$instance->trace();
    }

    /**
     * Valid and set instance
     *
     * @param $instance
     */
    protected static function setInstance($instance)
    {
        if (!($instance instanceof Tracer)) {
            throw new \InvalidArgumentException('$instance must be instance of Tracer');
        }

        static::$instance = $instance;
    }

    /**
     * Checks Tracer instance
     */
    protected static function checkInstance()
    {
        if (static::$instance === null) {
            throw new \BadMethodCallException('TracerProxy needs to be initialized first');
        }
    }
}

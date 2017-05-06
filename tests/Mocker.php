<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\AnnotationBlock;
use whitemerry\phpkin\Endpoint;
use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Logger\FileLogger;
use whitemerry\phpkin\Tracer;

/**
 * Class Mocker
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class Mocker
{
    /**
     * Init's tracer for TracerInfo
     */
    public static function initTracer()
    {
        new Tracer(
            'hut',
            static::getEndpoint(),
            new FileLogger(['path' => './'])
        );
    }

    /**
     * @return SpanIdentifier
     */
    public static function getIdentifier()
    {
        return new SpanIdentifier();
    }

    /**
     * @return Endpoint
     */
    public static function getEndpoint()
    {
        return new Endpoint(
            'partnership',
            '8.8.8.8',
            '80'
        );
    }

    /**
     * @return AnnotationBlock
     */
    public static function getAnnotationBlock()
    {
        return new AnnotationBlock(
            static::getEndpoint(),
            zipkin_timestamp() - 1000
        );
    }
}

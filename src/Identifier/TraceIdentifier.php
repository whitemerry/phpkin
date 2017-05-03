<?php
namespace whitemerry\phpkin\identifier;

/**
 * Class TraceIdentifier
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\identifier
 */
class TraceIdentifier extends Identifier
{
    /**
     * Generates 128-bit hex-encoded identifier
     * http://zipkin.io/pages/instrumenting.html#trace-identifiers
     *
     * @inheritdoc
     */
    protected function generate()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
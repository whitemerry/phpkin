<?php
namespace whitemerry\phpkin\Identifier;

/**
 * Class SpanIdentifier
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\identifier
 */
class SpanIdentifier extends Identifier
{
    /**
     * @inheritdoc
     *
     * @param $fromString string Optional, creates identifier from string
     */
    public function __construct($fromString = null)
    {
        if ($fromString && is_zipkin_span_identifier($fromString)) {
            $this->value = $fromString;
        } else {
            parent::__construct();
        }
    }

    /**
     * Generates 128-bit hex-encoded identifier
     * http://zipkin.io/pages/instrumenting.html#trace-identifiers
     *
     * @inheritdoc
     */
    protected function generate()
    {
        return bin2hex(openssl_random_pseudo_bytes(8));
    }
}

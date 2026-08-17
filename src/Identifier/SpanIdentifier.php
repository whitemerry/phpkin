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
     * Generates an identifier when given nothing. An absent B3 header reaches
     * $_SERVER as an empty string, so that counts as nothing too.
     *
     * @param $fromString string Optional, creates identifier from string
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($fromString = null)
    {
        if ($fromString === null || $fromString === '') {
            parent::__construct();
            return;
        }

        if (!is_zipkin_span_identifier($fromString)) {
            throw new \InvalidArgumentException('$fromString must be a valid span identifier');
        }

        $this->value = $fromString;
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

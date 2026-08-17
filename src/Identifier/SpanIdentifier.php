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
     * Generates an identifier when given nothing at all. Anything else has to
     * be a valid span identifier, an empty string included - a header that was
     * sent empty is a caller that got it wrong, not a caller that said nothing.
     *
     * @param $fromString string Optional, creates identifier from string
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($fromString = null)
    {
        if ($fromString === null) {
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

<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Identifier\TraceIdentifier;

/**
 * Class IdentifierTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class IdentifierTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateTraceIdentifier()
    {
        // when
        $identifier = new TraceIdentifier();

        // then
        $this->assertTrue(ctype_xdigit((string) $identifier));
        $this->assertSame(32, strlen((string) $identifier));
    }

    /**
     * @test
     */
    public function shouldCreateSpanIdentifier()
    {
        // when
        $identifier = new SpanIdentifier();

        // then
        $this->assertTrue(ctype_xdigit((string) $identifier));
        $this->assertSame(16, strlen((string) $identifier));
    }
}

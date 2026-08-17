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
class IdentifierTestCase extends TestCase
{
    const TRACE_ID = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    const SPAN_ID = 'bbbbbbbbbbbbbbbb';

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

    /**
     * @test
     */
    public function shouldCreateTraceIdentifierFromString()
    {
        // when
        $identifier = new TraceIdentifier(static::TRACE_ID);

        // then
        $this->assertSame(static::TRACE_ID, (string) $identifier);
    }

    /**
     * @test
     */
    public function shouldCreateSpanIdentifierFromString()
    {
        // when
        $identifier = new SpanIdentifier(static::SPAN_ID);

        // then
        $this->assertSame(static::SPAN_ID, (string) $identifier);
    }

    /**
     * @test
     */
    public function shouldFailOnInvalidTraceIdentifier()
    {
        // then
        $this->expectExceptionWithMessage('InvalidArgumentException', '$fromString');

        // when
        new TraceIdentifier('plaster');
    }

    /**
     * @test
     */
    public function shouldFailOnInvalidSpanIdentifier()
    {
        // then
        $this->expectExceptionWithMessage('InvalidArgumentException', '$fromString');

        // when
        new SpanIdentifier('plaster');
    }

    /**
     * A span identifier is not a valid trace identifier is not a span identifier,
     * so the lengths are not interchangeable
     *
     * @test
     */
    public function shouldFailOnSpanIdentifierOfTraceIdentifierLength()
    {
        // then
        $this->expectExceptionWithMessage('InvalidArgumentException', '$fromString');

        // when
        new SpanIdentifier(static::TRACE_ID);
    }

    /**
     * An absent header has no $_SERVER key at all, so it arrives as null. An
     * empty string means the header was sent and was empty, which is a caller
     * that got it wrong rather than a caller that said nothing
     *
     * @test
     */
    public function shouldFailOnEmptySpanIdentifier()
    {
        // then
        $this->expectExceptionWithMessage('InvalidArgumentException', '$fromString');

        // when
        new SpanIdentifier('');
    }
}

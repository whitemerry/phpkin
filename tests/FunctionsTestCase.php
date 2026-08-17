<?php
namespace whitemerry\phpkin\tests;

/**
 * Class FunctionsTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class FunctionsTestCase extends TestCase
{
    /**
     * @test
     */
    public function shouldGenerateAcceptableTimestamp()
    {
        // when
        $timestamp = zipkin_timestamp();

        // then
        $this->assertTrue(is_zipkin_timestamp($timestamp));
    }

    /**
     * @test
     */
    public function shouldGenerateCurrentTimeInMicroseconds()
    {
        // given
        // A float holds the value on every platform, unlike an int on 32-bit.
        $now = microtime(true) * 1000 * 1000;

        // when
        $timestamp = (float) zipkin_timestamp();

        // then
        $this->assertLessThan(1000 * 1000, abs($timestamp - $now));
    }
}

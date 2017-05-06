<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Span;

/**
 * Class SpanTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class SpanTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreate()
    {
        // given
        Mocker::initTracer();

        // when
        $span = new Span(
            Mocker::getIdentifier(),
            'plaster',
            Mocker::getAnnotationBlock()
        );
        $output = $span->toArray();

        // then
        $this->assertArrayHasKey('id', $output);
        $this->assertArrayHasKey('name', $output);
        $this->assertArrayHasKey('annotations', $output);
        $this->assertArrayHasKey('traceId', $output);
        $this->assertArrayHasKey('parentId', $output);
    }
}

<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Span;
use whitemerry\phpkin\Tracer;
use whitemerry\phpkin\TracerProxy;

/**
 * Class TracerProxyTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class TracerProxyTestCase extends TestCase
{
    /**
     * @test
     */
    public function shouldExposeSpansOfTracer()
    {
        // given
        $tracer = new Tracer('hut', Mocker::getEndpoint(), new SpyLogger());
        TracerProxy::init($tracer);

        $span = new Span(
            Mocker::getIdentifier(),
            'plaster',
            Mocker::getAnnotationBlock()
        );

        // when
        TracerProxy::addSpan($span);

        // then
        $this->assertSame(array($span), TracerProxy::getSpans());
    }
}

<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Span;
use whitemerry\phpkin\Tracer;

/**
 * Class TracerTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class TracerTestCase extends TestCase
{
    /**
     * @test
     */
    public function shouldExposeAddedSpans()
    {
        // given
        $tracer = new Tracer('hut', Mocker::getEndpoint(), new SpyLogger());
        $span = new Span(
            Mocker::getIdentifier(),
            'plaster',
            Mocker::getAnnotationBlock()
        );

        // when
        $tracer->addSpan($span);

        // then
        $this->assertSame(array($span), $tracer->getSpans());
    }

    /**
     * @test
     */
    public function shouldSerializeSpansForLogger()
    {
        // given
        $logger = new SpyLogger();
        $tracer = new Tracer('hut', Mocker::getEndpoint(), $logger);
        $span = new Span(
            Mocker::getIdentifier(),
            'plaster',
            Mocker::getAnnotationBlock()
        );
        $tracer->addSpan($span);

        // when
        $tracer->trace();

        // then
        $traced = $logger->getTracedSpans();
        $this->assertSame($span->toArray(), $traced[0]);
    }

    /**
     * @test
     */
    public function shouldAppendTraceSpanOnTrace()
    {
        // given
        $tracer = new Tracer('hut', Mocker::getEndpoint(), new SpyLogger());
        $tracer->addSpan(new Span(
            Mocker::getIdentifier(),
            'plaster',
            Mocker::getAnnotationBlock()
        ));

        // when
        $this->assertCount(1, $tracer->getSpans());
        $tracer->trace();

        // then
        $spans = $tracer->getSpans();
        $this->assertCount(2, $spans);

        $traceSpan = $spans[1]->toArray();
        $this->assertSame('hut', $traceSpan['name']);
    }

    /**
     * @test
     */
    public function shouldNotCollectSpansWhenNotSampled()
    {
        // given
        $tracer = new Tracer('hut', Mocker::getEndpoint(), new SpyLogger(), false);

        // when
        $tracer->addSpan(new Span(
            Mocker::getIdentifier(),
            'plaster',
            Mocker::getAnnotationBlock()
        ));

        // then
        $this->assertSame(array(), $tracer->getSpans());

        // TracerInfo keeps 'sampled' in a static and phpunit.xml does not back
        // static attributes up, so hand the next test a sampled trace
        Mocker::initTracer();
    }

    /**
     * @test
     */
    public function shouldFailOnSpan()
    {
        // given
        $this->expectExceptionWithMessage('InvalidArgumentException', '$span');

        $tracer = new Tracer('hut', Mocker::getEndpoint(), new SpyLogger());

        // then
        $tracer->addSpan('plaster');
    }
}

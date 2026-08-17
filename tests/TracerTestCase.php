<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Identifier\TraceIdentifier;
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
    const TRACE_ID = 'aaaaaaaaaaaaaaaa';
    const TRACE_SPAN_ID = 'bbbbbbbbbbbbbbbb';
    const PARENT_SPAN_ID = 'cccccccccccccccc';

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

    /**
     * @test
     */
    public function shouldTraceRootSpanWhenNothingPropagated()
    {
        // given
        $logger = new SpyLogger();
        $tracer = new Tracer('hut', Mocker::getEndpoint(), $logger);

        // when
        $tracer->trace();

        // then
        $this->assertArrayNotHasKey('parentId', $this->getTraceSpan($logger));
    }

    /**
     * @test
     */
    public function shouldParentTraceSpanToPropagatedParentSpanId()
    {
        // given
        $logger = new SpyLogger();
        $tracer = new Tracer(
            'hut',
            Mocker::getEndpoint(),
            $logger,
            true,
            new TraceIdentifier(static::TRACE_ID),
            new SpanIdentifier(static::TRACE_SPAN_ID),
            new SpanIdentifier(static::PARENT_SPAN_ID)
        );

        // when
        $tracer->trace();

        // then
        $traceSpan = $this->getTraceSpan($logger);
        $this->assertSame(static::TRACE_ID, $traceSpan['traceId']);
        $this->assertSame(static::TRACE_SPAN_ID, $traceSpan['id']);
        $this->assertSame(static::PARENT_SPAN_ID, $traceSpan['parentId']);
    }

    /**
     * @test
     */
    public function shouldTraceRootSpanWhenParentSpanIdMissing()
    {
        // given
        $logger = new SpyLogger();
        $tracer = new Tracer(
            'hut',
            Mocker::getEndpoint(),
            $logger,
            true,
            new TraceIdentifier(static::TRACE_ID),
            new SpanIdentifier(static::TRACE_SPAN_ID)
        );

        // when
        $tracer->trace();

        // then
        $this->assertArrayNotHasKey('parentId', $this->getTraceSpan($logger));
    }

    /**
     * @test
     */
    public function shouldNeverMakeTraceSpanItsOwnParent()
    {
        // given
        $logger = new SpyLogger();
        $tracer = new Tracer(
            'hut',
            Mocker::getEndpoint(),
            $logger,
            true,
            new TraceIdentifier(static::TRACE_ID),
            new SpanIdentifier(static::TRACE_SPAN_ID)
        );

        // when
        $tracer->trace();

        // then
        $traceSpan = $this->getTraceSpan($logger);
        $this->assertFalse(
            isset($traceSpan['parentId']) && $traceSpan['parentId'] === $traceSpan['id'],
            'Trace span must not reference itself as its own parent'
        );
    }

    /**
     * @test
     */
    public function shouldParentAddedSpansToTraceSpan()
    {
        // given
        $logger = new SpyLogger();
        $tracer = new Tracer(
            'hut',
            Mocker::getEndpoint(),
            $logger,
            true,
            new TraceIdentifier(static::TRACE_ID),
            new SpanIdentifier(static::TRACE_SPAN_ID),
            new SpanIdentifier(static::PARENT_SPAN_ID)
        );
        $tracer->addSpan(new Span(
            Mocker::getIdentifier(),
            'plaster',
            Mocker::getAnnotationBlock()
        ));

        // when
        $tracer->trace();

        // then
        $traced = $logger->getTracedSpans();
        $this->assertSame(static::TRACE_SPAN_ID, $traced[0]['parentId']);
    }

    /**
     * Span Tracer builds for the request itself, appended last by trace()
     *
     * @param $logger SpyLogger
     *
     * @return array
     */
    protected function getTraceSpan($logger)
    {
        $traced = $logger->getTracedSpans();

        return $traced[count($traced) - 1];
    }
}

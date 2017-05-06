<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\AnnotationBlock;
use whitemerry\phpkin\Endpoint;

/**
 * Class AnnotationBlockTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class AnnotationBlockTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreate()
    {
        // given
        $endpoint = new Endpoint('squash', '8.8.8.8', '10');
        $duration = 1000;
        $endTimestamp = zipkin_timestamp();
        $startTimestamp = $endTimestamp - $duration;

        // when
        $annotationBlock = new AnnotationBlock($endpoint, $startTimestamp, $endTimestamp);
        $output = $annotationBlock->toArray();

        // then
        $this->assertSame($duration, $annotationBlock->getDuration());
        $this->assertSame($startTimestamp, $annotationBlock->getStartTimestamp());
        $this->assertCount(2, $output);
        foreach ($output as $element) {
            $this->assertArrayHasKey('endpoint', $element);
            $this->assertArrayHasKey('timestamp', $element);
            $this->assertArrayHasKey('value', $element);
        }
    }

    /**
     * @test
     */
    public function shouldCreateWithEndTime()
    {
        // given
        $endpoint = new Endpoint('pat', '8.8.4.4', '1024');
        $duration = 2048;
        $endTimestamp = zipkin_timestamp();
        $startTimestamp = $endTimestamp - $duration;
        $type = AnnotationBlock::SERVER;

        // when
        $annotationBlock = new AnnotationBlock($endpoint, $startTimestamp, $endTimestamp, $type);

        // then
        $this->assertSame($duration, $annotationBlock->getDuration());
    }

    /**
     * @test
     */
    public function shouldFailOnType()
    {
        // given
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/\$type/');

        $type = 'city';

        // then
        new AnnotationBlock(
            new Endpoint('designer', '8.8.2.2', '64'),
            zipkin_timestamp(),
            null,
            $type
        );
    }

    /**
     * @test
     */
    public function shouldFailOnEndpoint()
    {
        // given
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/\$endpoint/');

        $endpoint = 'pneumonia';

        // then
        new AnnotationBlock(
            $endpoint,
            zipkin_timestamp()
        );
    }

    /**
     * @test
     */
    public function shouldFailOnTimestamp()
    {
        // given
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/startTimestamp/');

        $startTimestamp = 1234;

        // then
        new AnnotationBlock(
            new Endpoint('horn', '127.0.1.1', '8080'),
            $startTimestamp
        );
    }
}

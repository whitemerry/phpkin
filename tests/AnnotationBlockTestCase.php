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
class AnnotationBlockTestCase extends TestCase
{
    /**
     * @test
     */
    public function shouldCreate()
    {
        // given
        $endpoint = new Endpoint('squash', '8.8.8.8', '10');
        $duration = 1000;
        // Fixed, because a timestamp is too wide for int arithmetic on 32-bit.
        $startTimestamp = '1500000000000000';
        $endTimestamp = '1500000000001000';

        // when
        $annotationBlock = new AnnotationBlock($endpoint, $startTimestamp, $endTimestamp);
        $output = $annotationBlock->toArray();

        // then
        // Not assertSame: the difference is a float on 32-bit builds.
        $this->assertEquals($duration, $annotationBlock->getDuration());
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
        $startTimestamp = '1500000000000000';
        $endTimestamp = '1500000000002048';
        $type = AnnotationBlock::SERVER;

        // when
        $annotationBlock = new AnnotationBlock($endpoint, $startTimestamp, $endTimestamp, $type);

        // then
        // Not assertSame: the difference is a float on 32-bit builds.
        $this->assertEquals($duration, $annotationBlock->getDuration());
    }

    /**
     * @test
     */
    public function shouldFailOnType()
    {
        // given
        $this->expectExceptionWithMessage('InvalidArgumentException', '$type');

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
        $this->expectExceptionWithMessage('InvalidArgumentException', '$endpoint');

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
        $this->expectExceptionWithMessage('InvalidArgumentException', 'startTimestamp');

        $startTimestamp = 1234;

        // then
        new AnnotationBlock(
            new Endpoint('horn', '127.0.1.1', '8080'),
            $startTimestamp
        );
    }
}

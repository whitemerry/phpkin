<?php
namespace whitemerry\phpkin\tests\Sampler;

use whitemerry\phpkin\Sampler\PercentageSampler;

/**
 * Class PercentageSamplerTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests\Sampler
 */
class PercentageSamplerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCalculate()
    {
        // given
        $percents = 51;

        // when
        $sampler = new PercentageSampler(array('percents' => $percents));

        // then
        $this->assertTrue(is_bool($sampler->isSampled()));
    }

    /**
     * @test
     */
    public function shouldNotBeSampled()
    {
        // given
        $percents = 0;

        // when
        $sampler = new PercentageSampler(array('percents' => $percents));

        // then
        $this->assertFalse($sampler->isSampled());
    }

    /**
     * @test
     */
    public function shouldBeSampled()
    {
// given
        $percents = 100;

        // when
        $sampler = new PercentageSampler(array('percents' => $percents));

        // then
        $this->assertTrue($sampler->isSampled());
    }
}

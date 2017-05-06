<?php
namespace whitemerry\phpkin\Sampler;

/**
 * Class PercentageSampler
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\Sampler
 */
class PercentageSampler implements Sampler
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @inheritdoc
     *
     * $options
     *      ['percents'] int From 0 to 100 chance to trace
     *
     * @param $options array See above
     */
    public function __construct($options = [])
    {
        $defaults = [
            'percents' => 50
        ];

        $this->options = array_merge($defaults, $options);
    }

    /**
     * @inheritdoc
     */
    public function isSampled()
    {
        return mt_rand(0, 99) < $this->options['percents'];
    }
}

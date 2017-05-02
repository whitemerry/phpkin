<?php
namespace whitemerry\phpkin\Sampler;

/**
 * Class DefaultSampler
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\Sampler
 */
class DefaultSampler implements Sampler
{
    /**
     * @inheritdoc
     */
    public function __construct($options = [])
    {
    }

    /**
     * @inheritdoc
     */
    public function isSampled()
    {
        return true;
    }
}
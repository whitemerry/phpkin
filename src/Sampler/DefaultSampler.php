<?php
namespace whitemerry\phpkin\Sampler;

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
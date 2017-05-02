<?php
namespace whitemerry\phpkin\Sampler;

interface Sampler
{
    /**
     * Sampler constructor.
     *
     * @param array $options
     */
    public function __construct($options = []);

    /**
     * Returns calculated flag
     *
     * @return bool
     */
    public function isSampled();
}
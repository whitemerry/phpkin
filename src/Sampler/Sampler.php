<?php
namespace whitemerry\phpkin\Sampler;

/**
 * Interface Sampler
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\Sampler
 */
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
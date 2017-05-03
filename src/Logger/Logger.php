<?php
namespace whitemerry\phpkin\Logger;

/**
 * Interface Logger
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin
 */
interface Logger
{
    /**
     * Logger constructor.
     *
     * @param $options
     */
    public function __construct($options = []);

    /**
     * @param $spans array
     */
    public function trace($spans);
}
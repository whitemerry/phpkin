<?php
namespace whitemerry\phpkin;

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
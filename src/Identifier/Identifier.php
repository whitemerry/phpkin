<?php
namespace whitemerry\phpkin\Identifier;

/**
 * Class Identifier
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin
 */
abstract class Identifier
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Identifier constructor.
     */
    public function __construct()
    {
        $this->value = $this->generate();
    }

    /**
     * Identifier to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Generates an identifier (used only in constructor)
     * @internal
     *
     * @return string
     */
    abstract protected function generate();
}

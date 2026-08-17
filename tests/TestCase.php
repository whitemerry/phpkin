<?php
namespace whitemerry\phpkin\tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function expectExceptionWithMessage($exception, $message)
    {
        if (method_exists(get_parent_class($this), 'expectException')) {
            $this->expectException($exception);
            $this->expectExceptionMessage($message);
            return;
        }

        $this->setExpectedException($exception, $message);
    }
}

<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Metadata;

/**
 * Class MetadataTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class MetadataTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreate()
    {
        // given
        $data = array(
            array('part', 'basket'),
            array('cabinet', false),
            array('perform', 'race'),
            array('chain', 1997)
        );

        // when
        $metadata = new Metadata();
        foreach ($data as $element) {
            $metadata->set($element[0], $element[1]);
        }
        $output = $metadata->toArray();

        // then
        $this->assertArrayContainsOnlyStrings($output);
        foreach ($output as $key => $element) {
            $this->assertSame($element['key'], $data[$key][0]);
            $this->assertSame($element['value'], (string) $data[$key][1]);
        }
    }

    /**
     * @test
     */
    public function shouldFailOnKey()
    {
        // given
        # FIXME: Class name constant is available in PHP 5.5 only
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/\$key/');

        $key = false;

        // when
        $metadata = new Metadata();

        // then
        $metadata->set($key, 'launch');
    }

    /**
     * @test
     */
    public function shouldFailOnValue()
    {
        // given
        # FIXME: Class name constant is available in PHP 5.5 only
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/\$value/');

        $value = new \stdClass();

        // when
        $metadata = new Metadata();

        // then
        $metadata->set('socialist', $value);
    }

    /**
     * Check keys and values for type
     *
     * @param $array
     */
    protected function assertArrayContainsOnlyStrings($array)
    {
        foreach ($array as $element) {
            if (is_array($element)) {
                $this->assertArrayContainsOnlyStrings($element);
            } else {
                $this->assertInternalType('string', $element);
            }
        }
    }
}

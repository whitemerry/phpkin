<?php
namespace whitemerry\phpkin\tests;

use whitemerry\phpkin\Endpoint;

/**
 * Class EndpointTestCase
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\tests
 */
class EndpointTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreate()
    {
        // given
        $serviceName = 'occupation';
        $host = '8.3.2.1';
        $port = '83';

        // when
        $endpoint = new Endpoint($serviceName, $host, $port);
        $output = $endpoint->toArray();

        // then
        $this->assertArrayHasKey('serviceName', $output);
        $this->assertSame($serviceName, $output['serviceName']);
        $this->assertArrayHasKey('ipv4', $output);
        $this->assertSame($host, $output['ipv4']);
        $this->assertArrayHasKey('port', $output);
        $this->assertSame($port, $output['port']);
    }

    /**
     * @test
     */
    public function shouldFailOnName()
    {
        // given
        # FIXME: Class name constant is available in PHP 5.5 only
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/\$serviceName/');

        $serviceName = true;

        // then
        new Endpoint($serviceName, '127.0.0.1', '80');
    }

    /**
     * @test
     */
    public function shouldFailOnIp()
    {
        // given
        # FIXME: Class name constant is available in PHP 5.5 only
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/\$ip/');

        $ip = 'quote';

        // then
        new Endpoint('way', $ip, '80');
    }

    /**
     * @test
     */
    public function shouldFailOnPort()
    {
        // given
        # FIXME: Class name constant is available in PHP 5.5 only
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/\$port/');

        $port = 'simplicity';

        // then
        new Endpoint('mourning', '127.0.0.1', $port);
    }
}

<?php
namespace whitemerry\phpkin\Logger;

/**
 * Class FileLogger
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\Logger
 */
class FileLogger implements Logger
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @inheritdoc
     *
     * $options
     *      ['path'] string Logs directory location
     *      ['fileName] string Zipkin logs file name
     *
     * @param $options array See above
     *
     * @throws \BadMethodCallException
     */
    public function __construct($options = [])
    {
        $defaults = [
            'path' => 'tmp' . DIRECTORY_SEPARATOR,
            'fileName' => 'zipkin.log'
        ];

        $this->options = array_merge($defaults, $options);

        if (!is_dir($this->options['path'])) {
            throw new LoggerException('Invalid logs directory');
        }
    }

    /**
     * @inheritdoc
     */
    public function trace($spans)
    {
        file_put_contents(
            $this->options['path'] . DIRECTORY_SEPARATOR . $this->options['fileName'],
            json_encode($spans) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
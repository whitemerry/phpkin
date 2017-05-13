<?php
namespace whitemerry\phpkin\Logger;

/**
 * Class SimpleHttpLogger
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin\Logger
 */
class SimpleHttpLogger implements Logger
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @inheritdoc
     *
     * $options
     *      ['host'] string Zipkin's host with port, schema and without trailing slash (default http://127.0.0.1:9411)
     *      ['endpoint'] string Zipkin's endpoint (default /api/v1/spans)
     *      ['muteErrors'] bool Mute exceptions on upload error (default true)
     *      ['contextOptions'] array More options for stream_context_create like ssl
     *
     * @param $options array See above
     *
     * @throws \BadMethodCallException
     */
    public function __construct($options = [])
    {
        $defaults = [
            'host' => 'http://127.0.0.1:9144',
            'endpoint' => '/api/v1/spans',
            'muteErrors' => true,
            'contextOptions' => []
        ];

        $this->options = array_merge($defaults, $options);
    }

    /**
     * @inheritdoc
     */
    public function trace($spans)
    {
        $contextOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode($spans),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create(array_merge_recursive($contextOptions, $this->options['contextOptions']));
        @file_get_contents($this->options['host'] . $this->options['endpoint'], false, $context);

        if (
            !$this->options['muteErrors']
            && (empty($http_response_header) || !$this->validResponse($http_response_header))
        ) {
            throw new LoggerException('Trace upload failed');
        }
    }

    /**
     * Search for 202 header
     *
     * @return bool
     */
    protected function validResponse($headers)
    {
        foreach ($headers as $header) {
            if (preg_match('/202/', $header)) {
                return true;
            }
        }

        return false;
    }
}

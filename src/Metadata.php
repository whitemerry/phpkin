<?php
namespace whitemerry\phpkin;

/**
 * Class Metadata (BinaryAnnotations block)
 *
 * Constants and descriptions from:
 * https://github.com/openzipkin/zipkin/blob/master/zipkin/src/main/java/zipkin/Constants.java
 * https://github.com/openzipkin/zipkin/blob/1d0e657e6f89b929a0be27db843a848611242b92/benchmarks/src/main/java/com/twitter/zipkin/thriftjava/zipkinCoreConstants.java
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin
 */
class Metadata
{
    /**
     * The domain portion of the URL or host header. Ex. "mybucket.s3.amazonaws.com"
     *
     * Used to filter by host as opposed to ip address.
     */
    const HTTP_HOST = 'http.host';

    /**
     * The HTTP method, or verb, such as "GET" or "POST".
     *
     * Used to filter against an http route.
     */
    const HTTP_METHOD = 'http.method';

    /**
     * The absolute http path, without any query parameters. Ex. "/objects/abcd-ff"
     *
     * Used to filter against an http route, portably with zipkin v1.
     *
     * Historical note: This was commonly expressed as "http.uri" in zipkin, eventhough it was most
     * often just a path.
     */
    const HTTP_PATH = 'http.path';

    /**
     * The entire URL, including the scheme, host and query parameters if available. Ex.
     * "https://mybucket.s3.amazonaws.com/objects/abcd-ff?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Algorithm=AWS4-HMAC-SHA256..."
     *
     * Combined with HTTP_METHOD, you can understand the fully-qualified request line.
     *
     * This is optional as it may include private data or be of considerable length.
     */
    const HTTP_URL = 'http.url';

    /**
     * The HTTP status code, when not in 2xx range. Ex. "503"
     *
     * Used to filter for error status.
     */
    const HTTP_STATUS_CODE = 'http.status_code';

    /**
     * This indicates when an error occurred.
     * Value is human readable message associated with an error.
     */
    const ERROR = 'error';

    /**
     * Local component name
     */
    const LOCAL_COMPONENT = 'lc';

    /**
     * @var array[]
     */
    protected $annotations = [];

    /**
     * Set meta annotation
     *
     * @param $key string
     * @param $value string|int|float|bool
     *
     * @throws \InvalidArgumentException
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('$key must be string');
        }

        if (!is_string($value) && !is_numeric($value) && !is_bool($value)) {
            throw new \InvalidArgumentException('$value must be string, int or bool');
        }

        $this->annotations[] = [
            'key' => $key,
            'value' => (string) $value
        ];
    }

    /**
     * Metadata to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->annotations;
    }
}

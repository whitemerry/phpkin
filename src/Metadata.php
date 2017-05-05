<?php
namespace whitemerry\phpkin;

/**
 * Class Metadata
 *
 * @author Piotr Bugaj <whitemerry@outlook.com>
 * @package whitemerry\phpkin
 */
class Metadata
{
    const HTTP_HOST = "http.host";
    const HTTP_METHOD = "http.method";
    const HTTP_PATH = "http.path";
    const HTTP_URL = "http.url";
    const HTTP_STATUS_CODE = "http.status_code";
    const ERROR = "error";

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

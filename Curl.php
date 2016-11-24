<?php

class Curl
{
    /**
     * @var string URL
     */
    protected $url;
    /**
     * @var array Default cURL options
     */
    protected static $defaultCurlOptions = [
        'autoreferer' => true,
        'followLocation' => true,
        'header' => false,
        'returnTransfer' => true,
        'sslVerifyPeer' => false,
        'timeout' => 10,
        'maxRedirects' => 3,
    ];

    /**
     * Curl constructor.
     *
     * @param string $url
     *
     * @throws \Exception
     */
    public function __construct($url)
    {
        $this->setUrl($url);
    }

    /**
     * Sets URL.
     *
     * @param string $url
     *
     * @return $this
     * @throws \Exception
     */
    public function setUrl($url)
    {
        if (!self::validateUrl($url)) {
            throw new CurlException("Invalid URL.");
        }

        $this->url = $url;

        return $this;
    }

    /**
     * @return string URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    final protected static function validateUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Do cURL request and returns response as array which contains
     * 'status_code', 'headers' & 'content' keys against corresponding values.
     *
     * @param null|string $url URL
     *
     * @return array
     * @throws \Exception
     */
    public function doRequest($url = null)
    {
        if (!is_null($url)) {
            $this->setUrl($url);
        }

        $curlOptions = array_merge(
            ['url' => $this->getUrl()],
            self::$defaultCurlOptions
        );
        $options = $this->composeCurlOptions($curlOptions);
        $curlResource = $this->init($options);

        $responseHeaders = [];
        $this->setHeaderOutput($curlResource, $responseHeaders);

        try {
            $responseContent = curl_exec($curlResource);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

        // check cURL error
        $errorNumber = curl_errno($curlResource);
        $errorMessage = curl_error($curlResource);
        $httpStatusCode = curl_getinfo($curlResource, CURLINFO_HTTP_CODE);

        curl_close($curlResource);

        if ($errorNumber > 0) {
            throw new CurlException('Curl error: #' . $errorNumber . ' - ' . $errorMessage);
        }

        return [
            'status_code' => $httpStatusCode,
            'headers' => $responseHeaders,
            'content' => $responseContent,
        ];
    }

    /**
     * Initializes cURL resource.
     *
     * @param array $curlOptions cURL options.
     *
     * @return resource prepared cURL resource.
     */
    final protected function init(array $curlOptions)
    {
        $curlResource = curl_init();
        foreach ($curlOptions as $option => $value) {
            curl_setopt($curlResource, $option, $value);
        }

        return $curlResource;
    }

    /**
     * Composes cURL options from raw request options.
     *
     * @param array $options raw request options.
     *
     * @return array cURL options, in format: [curl_constant => value].
     */
    final protected function composeCurlOptions(array $options)
    {
        static $optionMap = [
            'protocolVersion' => CURLOPT_HTTP_VERSION,
            'maxRedirects' => CURLOPT_MAXREDIRS,
            'sslCapath' => CURLOPT_CAPATH,
            'sslCafile' => CURLOPT_CAINFO,
        ];

        $curlOptions = [];
        foreach ($options as $key => $value) {
            if (is_int($key)) {
                $curlOptions[$key] = $value;
            } else {
                if (isset($optionMap[$key])) {
                    $curlOptions[$optionMap[$key]] = $value;
                } else {
                    $key = strtoupper($key);
                    if (strpos($key, 'SSL') === 0) {
                        $key = substr($key, 3);
                        $constantName = 'CURLOPT_SSL_' . $key;
                        if (!defined($constantName)) {
                            $constantName = 'CURLOPT_SSL' . $key;
                        }
                    } else {
                        $constantName = 'CURLOPT_' . strtoupper($key);
                    }
                    $curlOptions[constant($constantName)] = $value;
                }
            }
        }

        return $curlOptions;
    }

    /**
     * Setup a variable, which should collect the cURL response headers.
     *
     * @param resource $curlResource cURL resource.
     * @param array $output variable, which should collection headers.
     */
    final protected function setHeaderOutput($curlResource, array &$output)
    {
        curl_setopt($curlResource, CURLOPT_HEADERFUNCTION, function ($resource, $headerString) use (&$output) {
            $header = trim($headerString, "\n\r");
            if (strlen($header) > 0) {
                $output[] = $header;
            }

            return mb_strlen($headerString, '8bit');
        });
    }
}
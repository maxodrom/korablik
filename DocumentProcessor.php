<?php

/**
 * Class DocumentProcessor
 */
class DocumentProcessor
{
    /**
     * Instances types.
     */
    const TYPE_BASE = 'base';
    const TYPE_INVERSE = 'inversive';

    /**
     * URL
     *
     * @var string
     */
    public $url;
    /**
     * Document content as string.
     *
     * @var string
     */
    protected $content;

    /**
     * Returns Doc processor instance of the given type.
     *
     * @param null|string $type Document processor type.
     * @param null $url URL
     *
     * @return DocumentProcessor|InverseDocumentProcessor
     * @throws Exception
     */
    final public static function getInstance($type = null, $url = null)
    {
        if (get_called_class() !== self::class) {
            throw new \Exception(
                "Creation of new instances using child classes is forbidden. " .
                "You must instantiate a new processor object using DocumentProcessor::getInstance() method call only."
            );
        }

        if ($type === null || $type === self::TYPE_BASE) {
            return new self($url);
        } elseif ($type === self::TYPE_INVERSE) {
            return new InverseDocumentProcessor($url);
        } else {
            throw new \InvalidArgumentException(
                "You must specify any valid processor type."
            );
        }
    }

    /**
     * @inheritdoc
     *
     * @param string $url URL
     */
    private function __construct($url = null)
    {
        if (!is_null($url)) {
            $this->setUrl($url);
        }
    }

    /**
     * Sets URL.
     *
     * @param string $url URL
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = strval($url);

        return $this;
    }

    /**
     * Gets doc content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = strval($content);

        return $this;
    }

    /**
     * Fetch document content using URL.
     * URL should be set up via class interface or by using $url param of this method.
     * Returns array on fetch success (cURL has not any errors) which structure is as follows:
     * <code>
     * [
     *      'status_code' => 200, // HTTP response code
     *      'headers' => [...], // array with response headers
     *      'content' => '...', // response body content
     * ]
     * </code>
     *
     * @param null|string $url Any valid URL or null if URL was set up in any previous steps
     *
     * @return array
     * @throws \Exception|CurlException
     */
    public function fetch($url = null)
    {
        if (!is_null($url)) {
            $this->setUrl($url);
        }

        try {
            $curl = new Curl($this->url);
            $response = $curl->doRequest();

            return $response;
        } catch (CurlException $ce) {
            throw new CurlException($ce->getMessage(), $ce->getCode(), $ce);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Выполняет замещение вхождения строки $target ("цели") на ее замещение ($replacement),
     * а также использование пользовательской callback-функции (для кастомизации/"декорирования"
     * обработки строки-замещения).
     * Callback must be defined like follows:
     * <code>
     * function ($replacement) {
     *      // do something...
     *      return $result;
     * }
     * </code>
     *
     * @param string $target
     * @param string $replacement
     * @param callable|null $callback
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function replaceUsingPair($target, $replacement, callable $callback = null)
    {
        if (is_null($this->content)) {
            return $this;
        }

        if (!is_null($callback)) {
            $replacement = call_user_func($callback, $replacement);
        }

        /**
         * Предотвращение аномалий поиска и замены, когда $replacement ВКЛЮЧАЕТ (содержит)
         * в себе $target. Пример некорректной замены, приводящей к бесконечному циклу обработки:
         * "может быть" => "не может быть". В этом и других подобных случаях поиск и замена
         * никогда не остановятся по той причине, что замещение ($replacement) УЖЕ содержит
         * в себе то, ЧТО мы пытаемся заменить ($target), т.е. таргет и реплейсмент где-то полностью пересекаются.
         * Отсюда - порочный круг.
         * Поэтому нужно проверить наличие $target в $replacement и, например, сгенерировать исключение.
         */
        if (preg_match('/' . $target . '/u', $replacement)) {
            throw new \RuntimeException(
                "'$replacement' should not contain '$target' as its substring."
            );
        }

        do {
            $content = $this->getContent();
            $this->content = str_replace($target, $replacement, $this->getContent());
        } while ($content != $this->getContent());

        return $this;
    }

    /**
     * Выполняет множественные (конвейерные) замещения одних строк другими с использованием маппинга
     * и (опционально) user-defined callback-функции (для кастомизации вывода/обработки строк-замещений).
     *
     * Callback must be defined like follows:
     * <code>
     * function ($replacement) {
     *      // do something...
     *      return $result;
     * }
     * </code>
     * todo: тему с callback можно развивать и далее.
     *
     * @param array $mapping Mapping array with key => value pairs, where key is target, value is replacement
     * @param callable|null $callback
     *
     * @return $this
     */
    public function replaceUsingMapping(array $mapping, callable $callback = null)
    {
        if (is_null($this->content)) {
            return $this;
        }

        if (!empty($mapping)) {
            foreach ($mapping as $k => $v) {
                $this->replaceUsingPair($k, $v, $callback);
            }
        }

        return $this;
    }
}
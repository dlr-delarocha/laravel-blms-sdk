<?php
namespace BLMS;

use BLMS\Exceptions\BLMSResponseException;
use Closure;

class BLMSResponse
{
    /**
     * @var int The HTTP status code response from BLMS.
     */
    protected $httpStatusCode;

    /**
     * @var array The headers returned from BLMS.
     */
    protected $headers;

    /**
     * @var string The raw body of the response from BLMS.
     */
    protected $body;

    /**
     * @var array The decoded body of the BLMS response.
     */
    protected $decodedBody = [];

    /**
     * @var AuthRequest|BLMSRequest
     */
    protected $request;

    /**
     * @var BLMSResponseException
     */
    protected $thrownException;

    /**
     * @var string
     */
    protected $phrase;

    /**
     * BLMSResponse constructor.
     *
     * @param AuthRequest|BLMSRequest  $request
     * @param null $body
     * @param null $httpStatusCode
     * @param string $phrase
     * @param array $headers
     */
    public function __construct(
        $request,
        $body = null,
        $httpStatusCode = null,
        $phrase = '',
        array $headers = []
    ) {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->phrase = $phrase;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Decode body if there are a error then make an exception
     */
    private function decodeBody()
    {
        $this->decodedBody = json_decode($this->body, true);

        if ($this->isError() || is_null($this->decodedBody)) {
            $this->makeException();
        }
    }

    /**
     * Returns true if BLMS returned an error message.
     *
     * @return boolean
     */
    public function isError()
    {
        return isset($this->decodedBody['error']) || is_null($this->decodedBody);
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    private function makeException()
    {
        $this->thrownException = BLMSResponseException::create($this);
    }

    /**
     * @return array
     */
    public function getDecodedBody()
    {
        return $this->decodedBody;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int|null
     */
    public function getHttpCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    /**
     * @return BLMSRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return BLMSResponseException
     */
    public function getThrownException()
    {
        return $this->thrownException;
    }

    /**
     * develop mode
     * @param Closure $callback
     * @return $this
     */
    public function map(Closure $callback)
    {
        return new static(
            $this->request,
            array_map($callback, $this->getItems(), array_keys($this->getItems()))
        );
    }

    /**
     * develop mode
     * @return array|mixed
     */
    public function getItems()
    {
        if (!array_key_exists('data', $this->decodedBody)) {
            return array();
        }
        return current($this->decodedBody['data']);
    }
}

<?php
namespace BLMS\Exceptions;

use BLMS\BLMSResponse;

class BLMSResponseException extends BLMSSDKException
{
    private $response;
    private $responseData;

    public function __construct(BLMSResponse $response, BLMSSDKException $previousException = null)
    {
        $this->response = $response;
        $this->responseData = $response->getDecodedBody();

        $errorMessage = $this->get('message', 'Unknown error from BLMS.');
        $errorCode = $this->get('status', -1);

        if (empty($this->responseData)) {
            $errorCode = $response->getHttpCode();
            $errorMessage = $response->getPhrase();
        }

        parent::__construct($errorMessage, $errorCode, $previousException);
    }

    private function get($key, $default = null)
    {
        if (isset($this->responseData['error'][$key])) {
            return $this->responseData['error'][$key];
        }
        return $default;
    }

    public static function create(BLMSResponse $response)
    {
        $data = $response->getDecodedBody();
        $code = isset($data['error']['status']) ? $data['error']['status'] : -1;
        $message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error from BLMS.';

        if (empty($data)) {
            $code = $response->getHttpCode();
            $message = $response->getPhrase();
        }

        if (isset($data['error']['error_subcode'])) {
           //sub codes
        }

        switch ($code) {
            case 404:
                return new static($response, new BLMSNotFoundResourceException($message, $code));
            case 422:
                return new static($response, new BLMSValidationException($message, $code));
        }

        //authentication error
        if (isset($data['error']['type']) && $data['error']['type'] === 'CredentialsException') {
            return new static($response, new BLMSAuthenticationException($message, $code));
        }

        // All others
        return new static($response, new BLMSSDKException($message, $code));
    }
}

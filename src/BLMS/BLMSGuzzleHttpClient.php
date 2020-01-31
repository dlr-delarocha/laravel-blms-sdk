<?php
namespace BLMS;

use BLMS\Exceptions\BLMSResponseException;
use BLMS\Exceptions\MissingEnvironmentVariablesException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class BLMSGuzzleHttpClient
{
    /**
     * @var \GuzzleHttp\Client The Guzzle client.
     */
    protected $guzzleClient;

    const BLMS_USER = 'BLMS_USER';

    const BLMS_PASSWORD = 'BLMS_PASSWORD';

    const BLMS_DOMAIN = 'BLMS_DOMAIN';

    /**
     * BLMSGuzzleHttpClient constructor.
     * @param \GuzzleHttp\Client|null $guzzleClient
     */
    public function __construct(Client $guzzleClient = null)
    {
        $this->guzzleClient = $guzzleClient ?: new Client();
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        if (empty(config('blms.domain'))) {
            throw new MissingEnvironmentVariablesException('BLMS Domain is require in BLMS config file.');
        }
        return  config('blms.domain') ;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        if (empty(config('blms.user'))) {
            throw new MissingEnvironmentVariablesException('BLMS User is require in blms config file.');
        }
        return  config('blms.user') ;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        if (empty(config('blms.password'))) {
            throw new MissingEnvironmentVariablesException('BLMS Password is require in BLMS config file.');
        }
        return  config('blms.password') ;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        if (!Cache::has('access_token')) {
            throw new MissingEnvironmentVariablesException('BLMS Token is require in request.');
        }
        return Cache::get('access_token');
    }

    /**
     * @return Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzleClient;
    }

    /**
     * @param BLMSRequest $request
     * @return array
     */
    public function prepareRequestMessage(BLMSRequest $request)
    {
        $url = $this->getBaseUrl(). $request->getEndpoint();
        $request->setHeaders([
            'Authorization' => "Bearer {$this->getToken()}"
        ]);

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders()
        ];
    }

    /**
     * @param BLMSRequest $request
     * @return BLMSResponse
     * @throws Exceptions\BLMSResponseException
     * @throws GuzzleException
     */
    public function sendRequest(BLMSRequest $request)
    {
        list($url, $method, $headers) = $this->prepareRequestMessage($request);

        try {
            $rawResponse = $this->guzzleClient->request($method, $url, $headers);
        } catch (RequestException $e) {
            $rawResponse = $e->getResponse();
        }

        $returnResponse = new BLMSResponse(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getStatusCode(),
            $rawResponse->getReasonPhrase(),
            $rawResponse->getHeaders()
        );

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }

    /**
     * @param AuthRequest $request
     * @throws Exceptions\BLMSResponseException
     * @throws GuzzleException
     */
    public function refreshToken(AuthRequest $request)
    {
        try {
            $rawResponse = $this->login();
        } catch (RequestException $e) {
            $rawResponse = $e->getResponse();
        }

        $returnResponse = new BLMSResponse(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getStatusCode(),
            $rawResponse->getReasonPhrase(),
            $rawResponse->getHeaders()
        );
        
        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }
        
        return $request->saveTokenFromResponse($returnResponse);
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function login()
    {
        return $this->getGuzzleClient()->request(
            'POST',
            $this->getBaseUrl() . '/login',
            [
                'form_params' => [
                    'email' => $this->getUser(),
                    'password' => $this->getPassword()
                ]
            ]
        );
    }
}

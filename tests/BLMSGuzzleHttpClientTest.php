<?php
namespace BLMS\Tests;

use BLMS\AuthRequest;
use BLMS\BLMS;
use BLMS\BLMSGuzzleHttpClient;
use BLMS\BLMSRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase as Test;

class BLMSGuzzleHttpClientTest extends Test
{
    protected $request;

    protected $authRequest;

    protected $config = [
        'method' => 'GET',
        'endpoint' => '/segments',
        'token' => 'foo_access_token',
        'params' => ['param1', 'param2']
    ];

    protected function setUp(): void
    {
        list($method, $endpoint, $token) = array_values($this->config);
        Cache::put('access_token', $token);
        $this->request = new BLMSRequest($method, $endpoint, $token);
        $this->authRequest = new AuthRequest($method, $endpoint);
    }

    public function testAnClientRequestEntityCanInstantiate()
    {
        $app = new BLMSGuzzleHttpClient();
        $this->assertInstanceOf('\BLMS\BLMSGuzzleHttpClient', $app);
    }

    public function testClientCanBeInjected()
    {
        $app = new BLMSGuzzleHttpClient();
        $this->assertInstanceOf('GuzzleHttp\Client', $app->getGuzzleClient());
    }

    public function testClientCanGetBaseURL()
    {
        $request = new BLMSGuzzleHttpClient();

        $this->assertEquals(getenv('BLMS_DOMAIN'), $request->getBaseUrl());
    }

    public function testClientCanPrepareHeadersToRequest()
    {
        $client = new BLMSGuzzleHttpClient();
        $response = $client->prepareRequestMessage($this->request);

        $this->assertEquals($response[0], $client->getBaseUrl() . $this->config['endpoint']);
        $this->assertEquals($response[1], $this->config['method']);
        $this->assertArrayHasKey('headers', $response[2]);
        $this->assertArrayHasKey('Authorization', $response[2]['headers']);
        $this->assertStringContainsString($response[2]['headers']['Authorization'], 'Bearer ' . $this->config['token']);
    }

    public function testClientTokenConflictsWillThrow()
    {
        $client = new BLMSGuzzleHttpClient();
        $this->expectException('\BLMS\Exceptions\BLMSResponseException');
        $this->expectErrorMessage('Invalid Token');
        $client->sendRequest($this->request);
    }

    public function testClientAuthenticationRefreshTokenWillThrow()
    {
        Config::set('blms.user', 'foo');
        $client = new BLMSGuzzleHttpClient();
        $this->expectException('\BLMS\Exceptions\BLMSResponseException');
        $this->expectErrorMessage('The provided credentials are invalid.');
        $this->expectExceptionCode(401);

        $client->refreshToken($this->authRequest);
    }

    public function testClientCanBeLoginAndMakeARequest()
    {
        Cache::forget('access_token');
        Config::set('blms.user', BLMSTestCredentials::$blsm_user);
        Config::set('blms.password', BLMSTestCredentials::$blms_password);
        Config::set('blms.domain', BLMSTestCredentials::$blms_domain);
       
        $response = (new BLMS())->getService()->get('/segments');


        $this->assertEquals(200, $response->getHttpCode());
        $this->assertArrayHasKey('data', $response->getDecodedBody());
    }
}

<?php
namespace BLMS\Tests\Exceptions;

use BLMS\BLMSRequest;
use BLMS\BLMSResponse;
use BLMS\Exceptions\BLMSResponseException;
use PHPUnit\Framework\TestCase as Test;

class BLMSResponseExceptionTest extends Test
{
    protected $request;

    protected $config = [
        'method' => 'POST',
        'endpoint' => '/login'
    ];

    protected function setUp(): void
    {
        list($method, $endpoint) = array_values($this->config);

        $this->request = new BLMSRequest($method, $endpoint, '5425w43wer35we43r54w35er4w35e4r');
    }

    public function testAuthenticationExceptions()
    {
        $params = [
            'error' => [
                'code' => 'INVALID_TOKEN',
                'status' => 401,
                'message' => 'The provided credentials are invalid. ',
                'type' => 'CredentialsException'
            ],
        ];

        $response = new BLMSResponse($this->request, json_encode($params), 401);
        $exception = BLMSResponseException::create($response);

        $this->assertInstanceOf('BLMS\Exceptions\BLMSAuthenticationException', $exception->getPrevious());
        $this->assertEquals(401, $exception->getCode());
        $this->assertEquals('The provided credentials are invalid. ', $exception->getMessage());

        $params['error']['status'] = 404;
        $params['error']['message'] = 'Unknown error from BLMS.';
        $response = new BLMSResponse($this->request, json_encode($params), 404);
        $exception = BLMSResponseException::create($response);

        $this->assertInstanceOf('BLMS\Exceptions\BLMSNotFoundResourceException', $exception->getPrevious());
        $this->assertEquals(404, $exception->getCode());
        $this->assertEquals('Unknown error from BLMS.', $exception->getMessage());
    }
}

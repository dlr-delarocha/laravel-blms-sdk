<?php
namespace BMLS\Test;

use BLMS\BLMSRequest;
use BLMS\BLMSResponse;
use PHPUnit\Framework\TestCase as Test;

class BLMSResponseTest extends Test
{
    /**
     * @var BLMSRequest
     */
    protected $request;

    protected function setUp() : void
    {
        $this->request = new BLMSRequest();
    }

    protected $config = [
        '{"id":"123","name":"Foo"}',
        200,
        'foo_phrase',
        ['foo_header']
    ];

    public function testAnEmptyResponseEntityCanInstantiate()
    {
        $response = new BLMSResponse($this->request);
        $this->assertInstanceOf('BLMS\BLMSResponse', $response);
    }

    public function testResponseCanGetAttributes()
    {
        list($body, $code, $phrase, $headers) = $this->config;
        $response = new BLMSResponse($this->request, $body, $code, $phrase, $headers);

        $this->assertEquals(['id' => 123, 'name' => 'Foo'], $response->getDecodedBody());
        $this->assertEquals($code, $response->getHttpCode());
        $this->assertEquals($phrase, $response->getPhrase());
        $this->assertEquals($headers, $response->getHeaders());
        $this->assertInstanceOf('\BLMS\BLMSRequest', $response->getRequest());
    }

    public function testResponseCanMakeAnErrors()
    {
        $response = new BLMSResponse($this->request, '{"error": {"status":"404","message":"Foo"}}');
        $this->assertInstanceOf('\BLMS\Exceptions\BLMSResponseException', $response->getThrownException());
    }
}

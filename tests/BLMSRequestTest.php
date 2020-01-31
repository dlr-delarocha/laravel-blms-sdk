<?php
namespace BLMS\Tests;

use BLMS\BLMSRequest;
use PHPUnit\Framework\TestCase as Test;

class BLMSRequestTest extends Test
{

    public function testAnEmptyRequestEntityCanInstantiate()
    {
        $app = new BLMSRequest();
        $this->assertInstanceOf('\BLMS\BLMSRequest', $app);
    }

    public function testRequestCanGetAttributes()
    {
        $request = new BLMSRequest('GET', '/foo_url', 'foo_token', ['param' => 'foo_param']);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/foo_url', $request->getEndpoint());
        $this->assertEquals(['param' => 'foo_param'], $request->getParams());
    }

    public function testRequestCanSetAttributes()
    {
        $request = new BLMSRequest();
        $request->setHeaders(['Auth', 'Cache']);
        $this->assertEquals(['headers' => ['Auth', 'Cache']], $request->getHeaders());
    }
}

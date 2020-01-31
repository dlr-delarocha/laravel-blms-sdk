<?php
namespace BLMS\Tests;

use BLMS\BLMS;
use PHPUnit\Framework\TestCase as Test;

class BLMSTest extends Test
{
    protected $request;

    protected $config = [
        'token' => 'foo_token',
        'endpoint' => 'segments'
    ];

    //classes

    public function testAnEmptyEntityCanInstantiate()
    {
        $app = new BLMS();
        $this->assertInstanceOf('\BLMS\BLMS', $app);
    }

    public function testAnClientRequestEntityCanInstantiate()
    {
        $app = new BLMS();
        $this->assertInstanceOf('\BLMS\BLMSGuzzleHttpClient', $app->getClient());
    }
}

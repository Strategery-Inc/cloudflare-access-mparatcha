<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccessTests;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Plus54\CloudFlareAccess\Service\TokenValidator;

final class TokenValidatorTest extends TestCase
{
    private $json;
    private $curlClient;

    public function setUp(): void
    {
        $this->json = $this->getMockBuilder(Json::class)->getMock();
        $this->curlClient = $this->getMockBuilder(Curl::class)->getMock();
    }

    public function testGetPublicKeys(): void
    {
        $url = 'http://example.com';
        $this->curlClient->expects($this->once())->method('get')->with($url);
        $tokenValidator = new TokenValidator($this->json, $this->curlClient);
        $res = $tokenValidator->getPublicKeys($url);
        //$this->assertSame(expected: )
    }
}

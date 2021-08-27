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
        $url = TokenValidator::CERTS_URL;
        $this->curlClient->expects($this->any())->method('get')->with($url);
        $this->curlClient->expects($this->any())->method('getBody')
            ->willReturn('{"foo": "bar"}');
        $this->json->expects($this->any())->method('unserialize')->with('{"foo": "bar"}')
            ->willReturn(["foo" => "bar"]);

        $tokenValidator = new TokenValidator($this->json, $this->curlClient);
        $res = $tokenValidator->getPublicKeys($url);
    }
    /**
     * @depends testGetPublicKeys
     * */
    public function testValidateToken(): void
    {
        $tokenValidator = new TokenValidator($this->json, $this->curlClient);
        //$tokenValidator->validateToken()
    }
}

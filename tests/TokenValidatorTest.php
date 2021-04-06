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
        //$jsonBody = '{"foo": "bar"}';
        //$url = 'http://example.com';
        $possibleApiResponses = [
            'http://example.com' => ['{"foo": "bar"}', ["foo" => "bar"]]
        ];
        $state = new \stdClass();
        $state->url = null;
        $this->json = $this->getMockBuilder(Json::class)->getMock();
        $this->curlClient = $this->getMockBuilder(Curl::class)->getMock();
        $this->curlClient->expects($this->any())->method('get')->with($this->callback(
            fn($arg) => $state->url = $arg
        ));
        $this->curlClient->expects($this->any())->method('getBody')
            ->willReturnCallback(fn () => $possibleApiResponses[$state->url][0]);
            //->willReturn($jsonBody);
        $this->json->expects($this->any())->method('unserialize')->with($possibleApiResponses[$state->url][0])
            ->willReturn($possibleApiResponses[$state->url][1]);
            //->with($jsonBody);
    }
//Gabi: run test
    public function testGetPublicKeys(): void
    {
        $tokenValidator = new TokenValidator($this->json, $this->curlClient);
        $res = $tokenValidator->getPublicKeys('http://example.com');
        $this->assertIsArray($res);
        $this->assertArrayHasKey('foo', $res);
    }
}

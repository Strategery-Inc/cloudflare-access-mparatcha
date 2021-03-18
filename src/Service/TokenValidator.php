<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use \Firebase\JWT\JWT;
use \Firebase\JWT\JWK;


class TokenValidator {

    private $curl;
    private $json;

    /**
     * TokenValidator constructor.
     * @param Json $json
     * @param Curl $curl
     */
    public function __construct(Json $json, Curl $curl) {
        $this->curl = $curl;
        $this->json = $json;
    }

    /**
     * @param $url
     * @return array
     */
    public function getPublicKeys(string $url): array
    {
        $this->curlClient->get($url);
        return $this->json->unserialize($this->curlClient->getBody());
    }

    /**
     * @param $token
     * @param $url
     * @param $algorithm
     * @return object
     */
    public function validateToken(string $token, string $url, string $algorithm) : object
    {
        $jwks = $this->getPublicKeys($url);
        return JWT::decode($token, JWK::parseKeySet($jwks), array($algorithm));
    }

}
<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Service;

use Magento\Framework\HTTP\Client\Curl;
use \Firebase\JWT\JWT;
use \Firebase\JWT\JWK;

class TokenValidator {

    protected $curl;

    /**
     * @param Curl $curl
     */
    public function __construct(Curl $curl) {
        $this->curl = $curl;
    }
    public function getPublicKeys($url): array
    {
        $this->curlClient->get($url);
        $jwks = json_decode($this->curlClient->getBody(), true);
        return $jwks;
    }

    public function getJWT($payload, $url, $algorithm) {
        $jwks = $this->getPublicKeys($url);
        $result = JWT::decode($payload, JWK::parseKeySet($jwks), array($algorithm));
        return $result;
    }

}
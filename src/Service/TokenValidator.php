<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Service;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Plus54\CloudFlareAccess\Helper\Config;

class TokenValidator
{
    public const ALGORITHM = 'RS256';

    private $curlClient;
    private $json;
    /**
     * @var Config
     */
    private Config $config;

    /**
     * TokenValidator constructor.
     * @param Json $json
     * @param Curl $curl
     */
    public function __construct(Json $json, Curl $curl, Config $config)
    {
        $this->curlClient = $curl;
        $this->json = $json;
        $this->config = $config;
    }

    public function getPublicKeyFake(): array
    {
        $publicKey = \json_decode(<<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8kGa1pSjbSYZVebtTRBLxBz5H
4i2p/llLCrEeQhta5kaQu/RnvuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t
0tyazyZ8JXw+KgXTxldMPEL95+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4
ehde/zUxo6UvS7UrBQIDAQAB
-----END PUBLIC KEY-----
EOD, true);
        return $publicKey;
    }
    /*    public function getPublicKeysFake(): array
        {

            $jwks = \json_decode(<<<EOD
        {
    "keys": [
    {
    "kty": "RSA",
    "e": "AQAB",
    "use": "sig",
    "kid": "ZKG1ockLVMd5ynqmWPaavMA23Ve9TJunU9VvLum5k1s",
    "n": "mLkHatFdXX0gR9k1m_uTVTbF-ZAzp6dxosAOF7OJyCjXQ8L2lxDPT0ZjyqVJ_JfX9cxOKOhluQ54y-Z367yvvJsI7pa6SQJY0jwiuetPQKO6m9hkTrOvEqwGKDPgkg_I8-QyGROPMTIhUE21c9Vz8O-jqysq_-zpdaOA3UVHASn4e4sscyY-XvWF0c_s73uaCfHOvLgTuNGd8LNjE0eCDgcGRNVqikPguY4kqWQoTv18RmS3v232j7oO6e1CVk_2xNiGFZlrVX-xDNyKatGhV4X3mib9BNfL5hQkWffpy_rpwnqADIz6oRO11fiYiKV4PX_HOjZqGon2FfbpiCb8SQ"
    }
    ]
    }
    EOD,
                true
            );

            return $jwks;
        }
    */
    /**
     * @param $url
     * @return array
     */
    public function getPublicKeys(): array
    {
        $this->curlClient->get($this->config->getCertsUrl());
        return $this->json->unserialize($this->curlClient->getBody());
    }

    /**
     * @param $token
     * @param $url
     * @param $algorithm
     * @return object
     */
    public function validateToken(string $token) : object
    {
        $jwks = $this->getPublicKeys();
        return JWT::decode($token, JWK::parseKeySet($jwks), [self::ALGORITHM]);
    }
}

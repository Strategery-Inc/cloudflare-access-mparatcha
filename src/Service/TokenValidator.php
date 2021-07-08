<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Service;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Plus54\CloudFlareAccess\Api\TokenValidatorInterface;
use Plus54\CloudFlareAccess\Helper\Config;

class TokenValidator implements TokenValidatorInterface
{
    public const ALGORITHM = 'RS256';

    private Curl $curlClient;
    private Json $json;
    /**
     * @var Config
     */
    private Config $config;

    /**
     * TokenValidator constructor.
     * @param Json $json
     * @param Curl $curl
     * @param Config $config
     */
    public function __construct(Json $json, Curl $curl, Config $config)
    {
        $this->curlClient = $curl;
        $this->json = $json;
        $this->config = $config;
    }

    public function getPublicKeys(): array
    {
        $this->curlClient->get($this->config->getCertsUrl());
        return $this->json->unserialize($this->curlClient->getBody());
    }

    public function validateToken(string $token) : object
    {
        $jwks = $this->getPublicKeys();
        return JWT::decode($token, JWK::parseKeySet($jwks), [self::ALGORITHM]);
    }
}

<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use \Firebase\JWT\JWT;
use \Firebase\JWT\JWK;


class TokenValidator {

    public const TEAM_DOMAIN = 'https://hyva.ancord.io';
    public const CERTS_URL = self::TEAM_DOMAIN .'/cdn-cgi/access/certs';
    public const ALGORITHM = 'RS256';

    private $curlClient;
    private $json;

    /**
     * TokenValidator constructor.
     * @param Json $json
     * @param Curl $curl
     */
    public function __construct(Json $json, Curl $curl) {
        $this->curlClient = $curl;
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
    public function validateToken(string $token) : object
    {
        $jwks = $this->getPublicKeys(self::CERTS_URL);
        return JWT::decode($token, JWK::parseKeySet($jwks), array(self::ALGORITHM));
    }

}
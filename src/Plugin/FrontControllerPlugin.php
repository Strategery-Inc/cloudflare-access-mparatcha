<?php

declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Plugin;

use Plus54\CloudFlareAccess\Service\TokenValidator;
use Magento\Framework\App\FrontController;


/**
 *
 * Intercept CloudFlare requests to check for CloudFlare Access token
 */
class FrontControllerPlugin
{
    public const TEAM_DOMAIN = 'https://hyva.ancord.io';
    public const CERTS_URL = self::TEAM_DOMAIN .'/cdn-cgi/access/certs';
    public const ALGORITHM = 'RS256';

    protected $serviceToken;

    /**
     * @param TokenValidator $serviceToken
     */
    public function __construct (TokenValidator $serviceToken ) {
        $this->serviceToken = $serviceToken;
    }

    public function aroundDispatch(FrontController $subject, callable $proceed)
    {
        $payload = []; //$this->getMockPayload();

        if (null === $payload) {
            throw new \Exception('missing required cf authorization token');
        }

        try {
            $result = $this->serviceToken->getJWT($payload, self::CERTS_URL, self::ALGORITHM);
            if ($result->iss !== "example.org") {
                throw new \Exception('Access denied.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Something was wrong, try again.');
        }
    }
}


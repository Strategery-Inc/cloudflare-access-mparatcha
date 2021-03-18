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

    protected $tokenValidator;

    /**
     * @param TokenValidator $tokenValidator
     */
    public function __construct (TokenValidator $tokenValidator )
    {
        $this->tokenValidator = $tokenValidator;
    }

    public function aroundDispatch(FrontController $subject, callable $proceed)
    {
//        $subject->get
        $token = $subject->getRequest()->getCookie('CF_Authorization');
        if (null === $token) {
            throw new \Exception('missing required cf authorization token');
        }

        try {
            $validatedToken = $this->tokenValidator->validateToken($token, self::CERTS_URL, self::ALGORITHM);
            if ($validatedToken->iss !== "example.org") {
                throw new \Exception('Access denied.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Something was wrong, try again.');
        }
    }
}


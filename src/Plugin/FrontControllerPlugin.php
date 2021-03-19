<?php

declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Plugin;

use Magento\Framework\App\RequestInterface;
use Plus54\CloudFlareAccess\Service\TokenValidator;
use Magento\Framework\App\FrontController;


/**
 *
 * Intercept CloudFlare requests to check for CloudFlare Access token
 */
class FrontControllerPlugin
{

    protected $tokenValidator;

    /**
     * @param TokenValidator $tokenValidator
     */
    public function __construct (TokenValidator $tokenValidator )
    {
        $this->tokenValidator = $tokenValidator;
    }

    public function aroundDispatch(FrontController $subject, callable $proceed, RequestInterface $request)
    {

        $token = $request->getCookie('CF_Authorization', null);
        if (null === $token) {
            throw new \Exception('missing required cf authorization token');
        }

        try {
            $validatedToken = $this->tokenValidator->validateToken($token);
            if ($validatedToken->iss !== "example.org") {
                throw new \Exception('Access denied.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Something was wrong, try again.');
        }
    }
}


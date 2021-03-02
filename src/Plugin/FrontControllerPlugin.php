<?php

declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Plugin;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;

/**
 *
 * Intercept CloudFlare requests to check for CloudFlare Access token
 */
class FrontControllerPlugin
{

    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        $cfAuthorizationCookie = $request->getCookie('CF_Authorization');
        $cfAuthorizationCookie = null;
        if(null === $cfAuthorizationCookie) {
           // throw new \Exception('request forbbiden outside of cloudflare access');
            die('Hola');
        }
        //validar token con libreria jwt
    }
}


<?php

declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Plugin;

use Magento\Framework\App\RequestInterface;
use Plus54\CloudFlareAccess\Api\TokenValidatorInterface;
use Magento\Framework\App\FrontController;
use Magento\Framework\Serialize\SerializerInterface;


/**
 *
 * Intercept CloudFlare requests to check for CloudFlare Access token
 */
class FrontControllerPlugin
{
    private $tokenValidator;
    private $serializer;

    /**
     * @param TokenValidatorInterface $tokenValidator
     */
    public function __construct (TokenValidatorInterface $tokenValidator, SerializerInterface $serializer)
    {
        $this->tokenValidator = $tokenValidator;
        $this->serializer = $serializer;
    }

    public function aroundDispatch(FrontController $subject, callable $proceed, RequestInterface $request)
    {
        return $proceed($request);
        /*try {
            $token = $request->getCookie('CF_Authorization', null);//siempre devuelve NULL
            if (is_null($token)) {
                throw new \Exception('missing required cf authorization token');
            }

            $validatedToken = $this->tokenValidator->validateToken($token);
            if ($validatedToken->iss !== "example.edu") { //?
                throw new \Exception('Access denied.');
            }

        } catch (\Exception $e) {
            throw $e;
        }*/


    }
}


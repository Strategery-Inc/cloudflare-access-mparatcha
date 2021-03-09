<?php

declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Plugin;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use \Firebase\JWT\JWT;
use \Firebase\JWT\JWK;

/**
 *
 * Intercept CloudFlare requests to check for CloudFlare Access token
 */
class FrontControllerPlugin
{
    public const TEAM_DOMAIN = 'https://cloudflare.com';
    public const CERTS_URL = self::TEAM_DOMAIN .'/cdn-cgi/access/certs';
    public const ALGORITHM = 'RS256';

    public function getPublicKeys(): array
    {
        //$this->curl->get(self::CERTS_URL);
        //request = '';

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

    public function getMockPayload(){
        // used https://mkjwk.org/ to generate test key,
        // then used https://8gwifi.org/jwkconvertfunctions.jsp to get PEM version 
        $privateKey = <<<EOD
        -----BEGIN RSA PRIVATE KEY-----
        MIIEowIBAAKCAQEAmLkHatFdXX0gR9k1m/uTVTbF+ZAzp6dxosAOF7OJyCjXQ8L2
        lxDPT0ZjyqVJ/JfX9cxOKOhluQ54y+Z367yvvJsI7pa6SQJY0jwiuetPQKO6m9hk
        TrOvEqwGKDPgkg/I8+QyGROPMTIhUE21c9Vz8O+jqysq/+zpdaOA3UVHASn4e4ss
        cyY+XvWF0c/s73uaCfHOvLgTuNGd8LNjE0eCDgcGRNVqikPguY4kqWQoTv18RmS3
        v232j7oO6e1CVk/2xNiGFZlrVX+xDNyKatGhV4X3mib9BNfL5hQkWffpy/rpwnqA
        DIz6oRO11fiYiKV4PX/HOjZqGon2FfbpiCb8SQIDAQABAoIBAFQHglBAj+lvfkJp
        /bgsTJ1XPMiakgFN/RU6LMbXrxileAO9kuX9hsMsjJ2kIjhL57RDTEHv1IBkuQwf
        a54WPG8+skRsRGUFWI+cLNM06G89ZuB2yIIRFWPlqKGYIZjb/IpM5U+s1l2QIopH
        p23rZRaNE/WLE+aqmK10X+PfSTkW4AvWLetKLIGRD1IAIYAx3dpuOpba8XmJpT+I
        LTnsM4Z6dzH3rVV89A7jYVXzJOEWxRl0xlLuqd3nlh6XXby6e2vuZzGsv+ty4Tic
        GjOwtmuputuxcuQq3nbo0xpBJB+Y8SvUcatcCOj3VqjDHhEpY/35AL/0ynfYjJ+Z
        Hd+fJrECgYEAysrtlCR/N7PD8fNvQvs7ecOYqpRkEohYW7SCHASyVVsRd6tCUrdc
        Dz0wJd8hT3tRCCG+GLHIXFi1yRWBE8NRK51JRV0IpBzqSPF/uhQe3W6IWqTi1ud0
        sEZRs2F1sEnXU2eMusmNoSpuhUdNDdxoL6upEa/lPp3VtmGQkheuRH0CgYEAwMsH
        +ai1aTm5z2z9GOcDs7xj/wbUO6g/gYo2dG3ThN6mQhmX3m42La5EKnFCOTNZQrx+
        zxrJwR2yXa7I6bc47Eq9HVKKFEUt9JhNzo7kKaLGmkkhUJC2dVd6S9x4m/kMNAsT
        nUPAidp9k8djrpgb76c8j2jIS8rVba/bosCf3L0CgYEAmqBexM2LXzqn1q3KkWUk
        9XRJzQ82utbRoKMjbh+6ptC4oemouY9sF4aNVuMq8ALUR4ILA6NTZe6SNdA6yons
        M3hLrSMB+ri9f3786DJ9UlP8jjkZacm0NNB5bXCLny6+i67yJF6YqmHDQcabH02G
        94pJcN4Qy0zn7pe3910tGLUCgYAjGFJ5VgbAAuwaIELqd5Mq8s5ZLEsSGQyGbjx/
        cUah1034lmQY74MZSbHK8BcYBornJR0IYl37s4Y2m4yjTuEj0m8emVndWsKE1fzD
        7ysFkUYJ6+oOmmk8bxIqIRYrfmiESMfnRuATuBxH/HHe5H2hYJYbnP1pHqE/eFXJ
        CLuRGQKBgFWEZFp4xEo1fMDlBgPyhOxEaarBwakUvsxl/k59k2mHWhfraSJiKoHd
        cmqxEF04smalcpea/h+Hyjr1A6GD6mPhrNuaGMVF01w0RtVasESW9pIyNzLRhz7S
        XAcoR7PYTgmjIeI6u3iryR/nWBVxs7NVCjXPFLVSmxJyE/zq6M1k
        -----END RSA PRIVATE KEY-----        
EOD;
        $payload = array(
            "iss" => "example.org",
            "aud" => "example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );

        // $jwt = JWT::encode($payload, $privateKey, 'RS256');
        return JWT::encode($payload, $privateKey, self::ALGORITHM);
    }
    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
      //  $payload = $request->getCookie('CF_Authorization');
        $payload = $this->getMockPayload(); //TODO: mock

        if (null === $payload) {
            throw new \Exception('missing required cf authorization token');
        }

        //validar token con libreria jwt
        $jwks = $this->getPublicKeys();
      //  try {
        // var_dump(json_encode($jwks));die;
        //$decoded = JWT::decode($jwt, $publicKey, array('RS256'));
            $result = JWT::decode($payload, JWK::parseKeySet($jwks), array('RS256'));
            if ($payload['iss'] !== "example.org") {
                //deny access
                var_dump('access denied');die;
            }
       // } catch (\Exception $e) {
       //     die('oops');
       // }
    }
}


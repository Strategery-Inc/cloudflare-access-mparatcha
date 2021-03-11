<?php

declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Plugin;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Client\Curl;
use \Firebase\JWT\JWT;
use \Firebase\JWT\JWK;


/**
 *
 * Intercept CloudFlare requests to check for CloudFlare Access token
 */
class FrontControllerPlugin
{
    public const TEAM_DOMAIN = 'https://hyva.ancord.io';
    public const CERTS_URL = self::TEAM_DOMAIN .'/cdn-cgi/access/certs';
    public const ALGORITHM = 'RS256';

    protected $curlClient;

    /**
     * @param Curl $curl
     */
    public function __construct (Curl $curl) {
        $this->curlClient = $curl;
    }

    public function getPublicKeys(): array
    {
        $this->curlClient->get('https://hyva.ancord.io/cdn-cgi/access/certs');
        $jwks = json_decode($this->curlClient->getBody(), true);
        return $jwks;
    }

    public function getMockPayload(){
        $privateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCwjCr73Dzznkph
cJjiCN+BPLaUqDtDIvqPaZIZ2foah0Mhh8XWBsvtHxpq7KZ/zIDKijUydqXEYlXL
ihIpbbmStTK8opW4vHgPBBxpQIK6GUhRNQep7z7QjF0GE7uMl2FU2+U3nrysWTlU
OSgDEl7gaXi/IBfRetmTtU3KKNl/hgpGkMPEuk8xReO3wJrIMA4an5uffS5j7ylu
8gcl7R+yVVn9pfEOtehGrmLlJP9jFrKMOMSfTK+VSrBsAedt1TRXfz1psVkYCn7h
EekiN79h/c8/Yb4UbxhtzWJ9rP4jm0Wat2/Br3CzfNqiaczXXUO3epQ++CzFFZw1
V/JN/IwfAgMBAAECggEABu/K9JBnuK68f22cJC1zErHe/qJJic7DB4V/XxKoeAhe
g9/AzzL/Kjbxo8feluDjIpIhmvT8eGXgtxmasxQDmZcTAkRlG5gUI4rmzt/hAwzh
QYeS4INs9Wa+dPGric+c3GezbucGxMLQtNV2FADRx4F9ZA0PwoRQLjSOdOX7/b2m
zpZTGvo6tdH52bro8ugkixpItZOnJESAWhxlgk0ZiJAo50yAeUBQOeQlGVxxh0Do
j790hjeLT56X354nYk6UgQyG5zjHYSBda2n4T25AW/X7R4hXrG+pr0GVA2RNa1Aq
qQtT+z7FvC+HIgPWr+CMHr7OrG20MWI2syXnxzyWMQKBgQDXPpyAUWV6anOYsFDa
57hQcGRntMygZEUyJu4GkX1Xx+lCj6kHIPTQYWhTSblydgQPZp9bcbSxMStIi1OJ
mQ2VDtEOtTRT/2Aai9rZOuHIZicuBzKaUOVcxSm5c7JfEZlFJ6mWwkXEgGhIU5Fs
jVQ/7AOO1PIW6TcF43Nf1pByFwKBgQDR+dLjx+cBgnQT+yHwA1qqjWrXq9JidjR+
B9EjZ++/Zjo8qmS6boVlONjNWEJnFABDcSemc60d/VWjgdWJbfxVDlG6nEMZYw4n
HRra39LaoO/boDZgbjRSSMY7FfokoFC+N2eee7WHt61YMbf+42m/kLdm61QQ+oyP
Oaybf9YjOQKBgQC0uRtKGg2sNQkN4KxiwEBfOZ7z/Df7S3VV4J3l4e5t76oIevqe
w6sJ819W3wXX9wL9s1qFuvjN9cyzwlfHpjxjNOePA9IF3NviLh74WZoNWsf2u5Bf
RSDvPZQE5AGWFP6ts9mOfVt6252zbIcjr55XdWCfYmhJmFLcnNbYVx08zQKBgFYP
i+U6MK9ItaTe4GkMJuQSdEETNnaOtjMVpx1Y40XSc3ob6I8U722uYAXB7+1poDYE
MdkiPkk1ZR1QAKKERtDhvpO6qjHEjK9xWWluJXDgV47v2nmpkZ5MH5tmBZFvd8iO
0tR9JckLemvUbPJ2aQhQGyt+toUC/AtT+y/8tFrZAoGAHnBcZnAJ/YUZD2Up9x95
FI2SuA3LeiHfHUtWKq/Tu/7LG9/JergcGvrexSkX6vditVWHSDrtnWf+3UZrf7LT
QnejtRYkikG25kwQFbBOjXFd776921fvIAsMie3Fnc6XUfiHAWelRgDgKL5lLcdy
5bLzkpAmi7xc8vqaQVJu9qw=
-----END PRIVATE KEY-----
EOD;
        $payload = array(
            "iss" => "example.edu",
            "aud" => "example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );

        return JWT::encode($payload, $privateKey, self::ALGORITHM, "871a7fabf77ffd290b497561e18fe76460b393a1fbc9442c7f53c54e5eebaa5e");
    }
    public function aroundDispatch(FrontController $subject, RequestInterface $request)
    {
        $payload = $this->getMockPayload();

        if (null === $payload) {
            throw new \Exception('missing required cf authorization token');
        }

        $jwks = $this->getPublicKeys();

        try {
            $result = JWT::decode($payload, JWK::parseKeySet($jwks), array('RS256'));
            if ($result->iss !== "example.org") {
                throw new \Exception('Access denied.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Something was wrong, try again.');
        }
    }
}


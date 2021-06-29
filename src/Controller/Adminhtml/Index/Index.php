<?php

namespace Plus54\CloudFlareAccess\Controller\Adminhtml\Index;

use Firebase\JWT\JWT;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Plus54\CloudFlareAccess\Service\LoginByCloudflareEmailService;
use Plus54\CloudFlareAccess\Service\LoginByCloudflareException;
use Plus54\CloudFlareAccess\Service\TokenValidator;

class Index implements HttpGetActionInterface
{
    private Context $context;
    private $tokenValidator;
    private $loginByCloudflareEmailService;
    protected $resultFactory;
    protected $auth;
    protected $backendUrl;

    /**
     *
     * @param Context $context
     * @param Auth $auth
     * @var LoginByCloudflareEmailService $loginByCloudflareEmailService
     * @var ResultFactory $resultFactory
     * @var TokenValidator $tokenValidator
     */
    public function __construct(
        Context $context,
        TokenValidator $tokenValidator,
        LoginByCloudflareEmailService $loginByCloudflareEmailService,
        ResultFactory $resultFactory,
        Auth $auth,
        UrlInterface $backendUrl
    ) {
        $this->context = $context;
        $this->tokenValidator = $tokenValidator;
        $this->loginByCloudflareEmailService = $loginByCloudflareEmailService;
        $this->resultFactory = $resultFactory;
        $this->auth = $auth;
        $this->backendUrl = $backendUrl;
    }

    public function execute()
    {
        try {
            // servicio => llamar al servicio TokenValidator
            // $token = $this->getMockPayload();

            // $validatedToken = $this->tokenValidator->validateToken($token);
            $this->loginByCloudflareEmailService->loginByEmail('gparatchaplus@plus54.com');


            // servicio => buscar y loguear al usuario que tiene el mismo email que el JWT token
            // redirigir al dashboard
            // catch LoginByCloudflareException
            // => set message
            // => redirect back to login page
        } catch (LoginByCloudflareException $e) {
            throw new \Exception(__('The user cannot login by Cloudflare properly'), 0, $e);
        }

        if ($this->auth->getAuthStorage()->isFirstPageAfterLogin()) {
            $this->auth->getAuthStorage()->setIsFirstPageAfterLogin(true);
        }

        //return $this->getRedirect($this->_backendUrl->getStartupPageUrl());
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->backendUrl->getStartupPageUrl());

        //$backendUrl = $this->getUrl('index');
        //$this->getRedirect($backendUrl);
        return $resultRedirect;
    }

    public function getRequest()
    {
        return $this->context->getRequest();
    }

    //FRONT-CONTROLLER-PLUGIN-TEST:
    public function getPublicKeys(): array
    {
        $jwks = \json_decode(
            <<<EOD
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

    public function getMockPayload()
    {
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

        $payload = [
            "iss" => "example.com",
            "aud" => "example.com",
            "email" => "jorge@example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000
        ];

        return JWT::encode($payload, $privateKey, 'RS256', "CYlS9DhnWY5ZTJUgS0T9EPBn27GOSe3_j9kvnIxrTvs");
    }
}

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
        $certs = '{"keys":[{"kid":"df99e0f760bebba63e3e40380e9fa0b50d328b512b3c0ff431aa1446699e80c4","kty":"RSA","alg":"RS256","use":"sig","e":"AQAB","n":"zEtLNxAsU4lbIUpGl9-gq134pU4kTrVF78v_u_hPJFVLw0lYSpa1-zsQ7DcVhH_N1b2SFVou2kZ9yr5hMIbqwB4d0CqGwc6NLJ9w9_vwAEDGijUOxkhDe28JjcBP5cftVSYggapAQdsr6jC0RA4Bxm3xiLywlUWBUWKB2vRb7ZicIUacBGDwCj_Ih8mOd4wik4NcESFrEL9_7P7z2d_bDfLgUkj0xZ2JdUI25uFE8XjDynhapDwXwQNtUfTFzAb9RiGzD52qnfMgqGWtxOw8Nh02a6vmyQdbQPRb45oZGzDPEDmmBTmPJZf-sSiI-5EsKcik8LLAewliddRYriL3Ow"},{"kid":"da8011e89ca97e85e3b2b695187468c520630fbd5c41bcf1399a46055783b052","kty":"RSA","alg":"RS256","use":"sig","e":"AQAB","n":"3s4Gi_ZmDkcX78f-o_tDHp46LU2PyWrh7yBuwNt9nxDzyq3EFX-BpO-iky6DyhSLOCqxRqr-yrqigZ1kLcn9RNEBR6Jl3v_8pP-hpoZRIfzvlu9-tV9pKI83oYHxocKZxbmsarhYMsInUnc11_ec_LyCHsyk-sG4UfAnq0D3SELhrr-xkJpoiO3JMlX4rZNQ_kMVT9waxbQiqQHVTNZ_bkfayLhKF9WgKxd2wSc-ZHbp4khgcFe0MImhtbktkDsghFv7C9d5LBF8zksADExzKQ7BscsmXawXRF6KtiQJMtx2pjuEub9oatRGqaTofxTwpvAq53uJLAVAmOqZ5JSwDw"}],"public_cert":{"kid":"da8011e89ca97e85e3b2b695187468c520630fbd5c41bcf1399a46055783b052","cert":"-----BEGIN CERTIFICATE-----\nMIIDUTCCAjmgAwIBAgIRAIsIV4kImyPkDIV+xeYT1+kwDQYJKoZIhvcNAQELBQAw\nYjELMAkGA1UEBhMCVVMxDjAMBgNVBAgTBVRleGFzMQ8wDQYDVQQHEwZBdXN0aW4x\nEzARBgNVBAoTCkNsb3VkZmxhcmUxHTAbBgNVBAMTFGNsb3VkZmxhcmVhY2Nlc3Mu\nY29tMB4XDTIxMDcwMzE2MTMzM1oXDTIxMDkwMTE2MTMzM1owYjELMAkGA1UEBhMC\nVVMxDjAMBgNVBAgTBVRleGFzMQ8wDQYDVQQHEwZBdXN0aW4xEzARBgNVBAoTCkNs\nb3VkZmxhcmUxHTAbBgNVBAMTFGNsb3VkZmxhcmVhY2Nlc3MuY29tMIIBIjANBgkq\nhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3s4Gi/ZmDkcX78f+o/tDHp46LU2PyWrh\n7yBuwNt9nxDzyq3EFX+BpO+iky6DyhSLOCqxRqr+yrqigZ1kLcn9RNEBR6Jl3v/8\npP+hpoZRIfzvlu9+tV9pKI83oYHxocKZxbmsarhYMsInUnc11/ec/LyCHsyk+sG4\nUfAnq0D3SELhrr+xkJpoiO3JMlX4rZNQ/kMVT9waxbQiqQHVTNZ/bkfayLhKF9Wg\nKxd2wSc+ZHbp4khgcFe0MImhtbktkDsghFv7C9d5LBF8zksADExzKQ7BscsmXawX\nRF6KtiQJMtx2pjuEub9oatRGqaTofxTwpvAq53uJLAVAmOqZ5JSwDwIDAQABowIw\nADANBgkqhkiG9w0BAQsFAAOCAQEAmk1IIZxhhAqAsSZu6qj8QMbnGMkgXN+qJRNT\nbsvs5E/jI2875NbRGp1WbNmvQbxP6VDh80Yx/i3LxAqhEQlSBSXLrCZnhyNDL6Sq\n/YNt2l2amS1oIy4kVGLpFLrlLonxwLJ6l+/dGDrJ14hIt7o/Lwsg1V6wCc8vvpsu\nJMdn3YocrAMz1lOJSZJE41Ik6DXZpptW4JmeCROutUEwUMQEPrOoHW5uvQVjR/Q6\nlDUaSVBkD6nrHHa2OQjVNxZcdX1wp3TJg2swZVQviMVQG/EVourg9iSYuvWAG+Mb\nhgXDES/Y8ePmxvTx1968fhkBuV17J+QOmKR0htjsDAw+XT08fQ==\n-----END CERTIFICATE-----\n"},"public_certs":[{"kid":"df99e0f760bebba63e3e40380e9fa0b50d328b512b3c0ff431aa1446699e80c4","cert":"-----BEGIN CERTIFICATE-----\nMIIDUDCCAjigAwIBAgIQPCd37u8XPtPP40o7bdtsuzANBgkqhkiG9w0BAQsFADBi\nMQswCQYDVQQGEwJVUzEOMAwGA1UECBMFVGV4YXMxDzANBgNVBAcTBkF1c3RpbjET\nMBEGA1UEChMKQ2xvdWRmbGFyZTEdMBsGA1UEAxMUY2xvdWRmbGFyZWFjY2Vzcy5j\nb20wHhcNMjEwNzAzMTYxMzMzWhcNMjEwOTAxMTYxMzMzWjBiMQswCQYDVQQGEwJV\nUzEOMAwGA1UECBMFVGV4YXMxDzANBgNVBAcTBkF1c3RpbjETMBEGA1UEChMKQ2xv\ndWRmbGFyZTEdMBsGA1UEAxMUY2xvdWRmbGFyZWFjY2Vzcy5jb20wggEiMA0GCSqG\nSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDMS0s3ECxTiVshSkaX36CrXfilTiROtUXv\ny/+7+E8kVUvDSVhKlrX7OxDsNxWEf83VvZIVWi7aRn3KvmEwhurAHh3QKobBzo0s\nn3D3+/AAQMaKNQ7GSEN7bwmNwE/lx+1VJiCBqkBB2yvqMLREDgHGbfGIvLCVRYFR\nYoHa9FvtmJwhRpwEYPAKP8iHyY53jCKTg1wRIWsQv3/s/vPZ39sN8uBSSPTFnYl1\nQjbm4UTxeMPKeFqkPBfBA21R9MXMBv1GIbMPnaqd8yCoZa3E7Dw2HTZrq+bJB1tA\n9FvjmhkbMM8QOaYFOY8ll/6xKIj7kSwpyKTwssB7CWJ11FiuIvc7AgMBAAGjAjAA\nMA0GCSqGSIb3DQEBCwUAA4IBAQCFoW5jhDuzNfi6pWsTRr3iXVaXBAqyqxfCESrf\ny23cE9p6BD6e496ZYb4z88jviJttNZDK9mS9oedPIiqptBLEa6a05vXiCWVOGORf\nusJrk916r9Wlew/ocXGRw2OPsAohr0I7m2Z/6ir1MfIsrO4Vgz8FvHjAKX4PsSJr\nuzCxAZujJ17lPtx5JVrWZaF7ywrXQ9byA3/9aCSwyhxL4J1O9d3OoEVIN1+MU3G7\nFK0QHV6yiFzvyZq4yFwxWhAP9VIBhd0/+HtJ5w3ulzjjbA4EJwK1hmIknPb06KpH\nlIl7kOKWyqFOMn6DbPOx3t8VBeE5J9Svylh8BEZm+e5elfGd\n-----END CERTIFICATE-----\n"},{"kid":"da8011e89ca97e85e3b2b695187468c520630fbd5c41bcf1399a46055783b052","cert":"-----BEGIN CERTIFICATE-----\nMIIDUTCCAjmgAwIBAgIRAIsIV4kImyPkDIV+xeYT1+kwDQYJKoZIhvcNAQELBQAw\nYjELMAkGA1UEBhMCVVMxDjAMBgNVBAgTBVRleGFzMQ8wDQYDVQQHEwZBdXN0aW4x\nEzARBgNVBAoTCkNsb3VkZmxhcmUxHTAbBgNVBAMTFGNsb3VkZmxhcmVhY2Nlc3Mu\nY29tMB4XDTIxMDcwMzE2MTMzM1oXDTIxMDkwMTE2MTMzM1owYjELMAkGA1UEBhMC\nVVMxDjAMBgNVBAgTBVRleGFzMQ8wDQYDVQQHEwZBdXN0aW4xEzARBgNVBAoTCkNs\nb3VkZmxhcmUxHTAbBgNVBAMTFGNsb3VkZmxhcmVhY2Nlc3MuY29tMIIBIjANBgkq\nhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3s4Gi/ZmDkcX78f+o/tDHp46LU2PyWrh\n7yBuwNt9nxDzyq3EFX+BpO+iky6DyhSLOCqxRqr+yrqigZ1kLcn9RNEBR6Jl3v/8\npP+hpoZRIfzvlu9+tV9pKI83oYHxocKZxbmsarhYMsInUnc11/ec/LyCHsyk+sG4\nUfAnq0D3SELhrr+xkJpoiO3JMlX4rZNQ/kMVT9waxbQiqQHVTNZ/bkfayLhKF9Wg\nKxd2wSc+ZHbp4khgcFe0MImhtbktkDsghFv7C9d5LBF8zksADExzKQ7BscsmXawX\nRF6KtiQJMtx2pjuEub9oatRGqaTofxTwpvAq53uJLAVAmOqZ5JSwDwIDAQABowIw\nADANBgkqhkiG9w0BAQsFAAOCAQEAmk1IIZxhhAqAsSZu6qj8QMbnGMkgXN+qJRNT\nbsvs5E/jI2875NbRGp1WbNmvQbxP6VDh80Yx/i3LxAqhEQlSBSXLrCZnhyNDL6Sq\n/YNt2l2amS1oIy4kVGLpFLrlLonxwLJ6l+/dGDrJ14hIt7o/Lwsg1V6wCc8vvpsu\nJMdn3YocrAMz1lOJSZJE41Ik6DXZpptW4JmeCROutUEwUMQEPrOoHW5uvQVjR/Q6\nlDUaSVBkD6nrHHa2OQjVNxZcdX1wp3TJg2swZVQviMVQG/EVourg9iSYuvWAG+Mb\nhgXDES/Y8ePmxvTx1968fhkBuV17J+QOmKR0htjsDAw+XT08fQ==\n-----END CERTIFICATE-----\n"}]}';
        return $certs;
    }

    public function getRequest()
    {
        return $this->context->getRequest();
    }
}

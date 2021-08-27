<?php

namespace Plus54\CloudFlareAccess\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const CLOUDFLAREACCESS_GENERAL_CERTS_URL = 'cloudflareaccess/general/certs_url';
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getCertsUrl(): string
    {
        return (string) $this->scopeConfig->getValue(self::CLOUDFLAREACCESS_GENERAL_CERTS_URL, ScopeInterface::SCOPE_STORE);
    }
}

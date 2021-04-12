<?php

namespace Plus54\CloudFlareAccess\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Index implements HttpGetActionInterface
{
    /** @var Context */
    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function execute()
    {
        die('My Backend Controller Works!');
    }

    public function getRequest()
    {
        return $this->context->getRequest();
    }
}
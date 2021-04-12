<?php

namespace Plus54\CloudFlareAccess\Controller\Adminhtml\Index;

use Magento\Framework\App\ActionInterface;
use Plus54\CloudFlareAccess\Service\TokenValidator;

class Index implements ActionInterface
{
    private $tokenValidator;

    public function __construct(
        TokenValidator $tokenValidator
    )
    {
        $this->tokenValidator = $tokenValidator;
    }

    public function execute()
    {

        die('My Backend Controller Works!');
    }
}
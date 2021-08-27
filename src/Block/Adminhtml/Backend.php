<?php

namespace Plus54\CloudFlareAccess\Block\Adminhtml;


use Magento\Backend\Block\Template;
use Magento\Backend\Helper\Data as DataHelper;

class Backend extends Template
{
    /**
     * Backend constructor.
     * @param Template\Context $context
     * @param array $data
     * @param DataHelper $dataHelper
     */
    public function __construct(
        DataHelper $dataHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    public function getAdminUrl(): string
    {
        $route = "cfaccess";
        $params = [];
        return $this->dataHelper->getUrl($route, $params);
    }
}

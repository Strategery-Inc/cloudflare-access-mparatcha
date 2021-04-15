<?php

namespace Plus54\CloudFlareAccess\Block;

use Magento\Framework\Mview\View\State\CollectionFactory;
use Magento\Framework\View\Element\Template;

class Backend extends Template
{
    private $collectionFactory;

    public function __construct(
        Template\Context $context,
        array $data = [],
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }
}
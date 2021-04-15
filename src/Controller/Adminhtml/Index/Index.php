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
        try {
            // servicio => llamar al servicio TokenValidator
            // servicio => buscar y loguear al usuario que tiene el mismo email que el JWT token
            // redirigir al dashboard
        } catch (\Exception $e) {
            // mostrar el mensaje de error en el frontend, usando "message manager"
        }
    }

    public function getRequest()
    {
        return $this->context->getRequest();
    }
}
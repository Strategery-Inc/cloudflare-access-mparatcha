<?php
namespace Plus54\CloudFlareAccess\Service;

use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;

class LoginByCloudflareException extends \Exception
{
    /**
     * @var \Magento\Framework\Phrase
     */
    protected $phrase;

    /**
     * @var string
     */
    protected $logMessage;

    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0)
    {
        $this->phrase = $phrase;
        parent::__construct($phrase->render(), (int)$code, $cause);
    }
    /**
     * Get the un-processed message, without the parameters filled in
     *
     * @return string
     */
    public function getRawMessage()
    {
        return $this->phrase->getText();
    }

    /**
     * Get parameters, corresponding to placeholders in raw exception message
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->phrase->getArguments();
    }

    /**
     * Get the un-localized message, but with the parameters filled in
     *
     * @return string
     */
    public function getLogMessage()
    {
        if ($this->logMessage === null) {
            $renderer = new Placeholder();
            $this->logMessage = $renderer->render([$this->getRawMessage()], $this->getParameters());
        }
        return $this->logMessage;
    }
}

<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Block;

use Elgentos\AlternateUrls\Type\TypeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class AlternateUrls extends Template
{
    private const XML_PATH_IS_ENABLED = 'alternate_urls/general/enabled';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->scopeConfig = $scopeConfig;
    }

    public function getTypeInstance(): ?TypeInterface
    {
        if (!$this->isEnabled()) {
            return null;
        }

        /** @var Http $request */
        $request       = $this->getRequest();
        $pageType      = $request->getFullActionName();
        $typeInstances = $this->getData('typeInstances');

        if (isset($typeInstances[$pageType])) {
            return $typeInstances[$pageType];
        }

        return $typeInstances['default'];
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}

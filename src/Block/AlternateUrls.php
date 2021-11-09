<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Block;

use Elgentos\AlternateUrls\Type\TypeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

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

        if (!$typeInstances || !isset($typeInstances['default'])) {
            return null;
        }

        if (
            isset($typeInstances[$pageType]) &&
            $typeInstances[$pageType] instanceof TypeInterface
        ) {
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

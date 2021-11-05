<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractType
{
    private Json $serializer;

    private ScopeConfigInterface $scopeConfig;

    protected StoreManagerInterface $storeManager;

    protected RequestInterface $request;

    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->serializer   = $serializer;
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->request      = $request;
    }

    protected function getMapping(): array
    {
        return $this->serializer->unserialize(
            $this->scopeConfig->getValue(
                'alternate_urls/general/mapping',
                ScopeInterface::SCOPE_STORE
            )
        );
    }

    protected function getCurrentUrlWithoutParameters(StoreInterface $store): string
    {
        $requestString = ltrim($this->request->getRequestString(), '/');
        $storeUrl      = $store->getBaseUrl();

        if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
            return $storeUrl;
        }

        $storeParsedUrl   = parse_url($storeUrl);
        $storeParsedQuery = [];

        if (isset($storeParsedUrl['query'])) {
            parse_str($storeParsedUrl['query'], $storeParsedQuery);
        }

        $currQuery = $this->request->getQueryValue();

        foreach ($currQuery as $key => $value) {
            $storeParsedQuery[$key] = $value;
        }

        $requestStringParts = explode('?', $requestString, 2);
        $requestStringPath  = $requestStringParts[0];

        return $storeParsedUrl['scheme']
            . '://'
            . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path']
            . $requestStringPath;
    }
}

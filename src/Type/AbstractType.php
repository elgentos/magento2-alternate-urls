<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
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

    public function getMapping(): array
    {
        return $this->serializer->unserialize(
            $this->scopeConfig->getValue(
                'alternate_urls/general/mapping',
                ScopeInterface::SCOPE_STORE
            )
        );
    }

    private function getRemoveStoreParam(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'alternate_urls/general/remove_store_param',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCurrentUrlWithoutParameters(Store $store): string
    {
        /** @var Http $request */
        $request       = $this->request;
        $requestString = ltrim($request->getRequestString(), '/');
        $storeUrl      = $store->getBaseUrl();

        if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
            return $storeUrl;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $storeParsedUrl     = parse_url($storeUrl);
        $requestStringParts = explode('?', $requestString, 2);
        $requestStringPath  = $requestStringParts[0];

        return $storeParsedUrl['scheme']
            . '://'
            . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path']
            . $requestStringPath;
    }

    public function modifyUrl(string $url): string
    {
        if (!$this->getRemoveStoreParam()) {
            return $url;
        }

        return explode($url, '?', 2)[0] ?? $url;
    }
}

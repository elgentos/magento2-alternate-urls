<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class DefaultType extends AbstractType implements TypeInterface, ArgumentInterface
{
    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        parent::__construct($serializer, $scopeConfig, $storeManager, $request);
    }

    public function getAlternateUrls(): array
    {
        $result  = [];
        $mapping = $this->getMapping();

        /** @var Store $currentStore */
        $currentStore = $this->storeManager->getStore();

        foreach ($mapping as $item) {
            $store = $currentStore->getId() === $item['store_id']
                ? $currentStore
                : $this->storeManager->getStore($item['store_id']);

            $result[] = [
                'hreflang' => $item['hreflang'],
                'url' => $this->getCurrentUrlWithoutParameters($store)
            ];
        }

        return $result;
    }
}

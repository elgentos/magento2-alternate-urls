<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class CmsPage extends AbstractType implements TypeInterface, ArgumentInterface
{
    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        parent::__construct($serializer, $scopeConfig, $storeManager, $request);

        $this->request = $request;
    }

    public function getAlternateUrls(): array
    {
        /** @var Store $currentStore */
        $currentStore = $this->storeManager->getStore();
        $result       = [];

        foreach ($this->getMapping() as $item) {
            // Because there is no actual link between CMS pages on different
            // stores we will only return the current store's URL
            if ($currentStore->getId() === $item['store_id']) {
                $result[] = [
                    'hreflang' => $item['hreflang'],
                    'url' => $this->getCurrentUrlWithoutParameters($currentStore)
                ];
            }
        }

        return $result;
    }
}

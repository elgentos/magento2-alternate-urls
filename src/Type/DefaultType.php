<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Elgentos\AlternateUrls\Model\AlternateUrl;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Store;

class DefaultType extends AbstractType implements TypeInterface, ArgumentInterface
{
    /**
     * @throws NoSuchEntityException
     */
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

            $result[] = new AlternateUrl(
                $item['hreflang'],
                $this->getCurrentUrlWithoutParameters($store)
            );
        }

        return $result;
    }
}

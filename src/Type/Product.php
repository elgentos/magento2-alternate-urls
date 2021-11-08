<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Product extends AbstractType implements TypeInterface, ArgumentInterface
{
    private ProductRepositoryInterface $productRepository;

    private Registry $registry;

    private ?ProductInterface $currentProduct = null;

    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Registry $registry,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($serializer, $scopeConfig, $storeManager, $request);

        $this->registry          = $registry;
        $this->productRepository = $productRepository;
    }

    public function getAlternateUrls(): array
    {
        $currentProduct = $this->getCurrentProduct();

        if (!$currentProduct || !$currentProduct->getId()) {
            return [];
        }

        $currentStore = $this->storeManager->getStore();
        $result       = [];

        foreach ($this->getMapping() as $item) {
            /** @var ProductInterface $product */
            try {
                $product = $currentStore->getId() === $item['store_id']
                    ? $currentProduct
                    : $this->productRepository->getById(
                        $currentProduct->getId(),
                        false,
                        $item['store_id']
                    );

                $result[] = [
                    'hreflang' => $item['hreflang'],
                    'url' => $product->getProductUrl()
                ];
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $result;
    }

    private function getCurrentProduct(): ?ProductInterface
    {
        if (!$this->currentProduct) {
            $this->currentProduct = $this->registry->registry('current_product');
        }

        return $this->currentProduct;
    }
}

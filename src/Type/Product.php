<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Elgentos\AlternateUrls\Model\AlternateUrl;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ProductModel;
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
        parent::__construct(
            $serializer,
            $scopeConfig,
            $storeManager,
            $request
        );

        $this->registry          = $registry;
        $this->productRepository = $productRepository;
    }

    public function getAlternateUrls(): array
    {
        $currentProduct = $this->getCurrentProduct();

        if (!$currentProduct || !$currentProduct->getId()) {
            return [];
        }

        return array_reduce(
            $this->getMapping() ?? [],
            function (array $carry, array $item) {
                try {
                    $product = $this->getStoreProduct((int) $item['store_id']);
                } catch (NoSuchEntityException $e) {
                    return $carry;
                }

                if ($product instanceof ProductInterface && $product->getId()) {
                    $carry[] = new AlternateUrl(
                        $item['hreflang'],
                        $this->modifyUrl($product->getProductUrl())
                    );
                }
                return $carry;
            },
            []
        );
    }

    private function getCurrentProduct(): ?ProductInterface
    {
        return $this->currentProduct ??= $this->registry->registry('current_product');
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getStoreProduct(
        int $storeId
    ): ?ProductInterface {
        $currentStore   = $this->storeManager->getStore();
        $currentProduct = $this->getCurrentProduct();
        $websiteId      = $this->getWebsiteByStoreId($storeId);
        $result         = null;

        if (!in_array($websiteId, $currentProduct->getWebsiteIds() ?? [], true)) {
            try {
                $result = $currentStore->getId() === $storeId
                    ? $currentProduct
                    : $this->productRepository->getById(
                        $currentProduct->getId(),
                        false,
                        $storeId
                    );
            } catch (NoSuchEntityException $e) {
                $result = null;
            }
        }

        return $result;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getWebsiteByStoreId(int $storeId): int
    {
        return (int) $this->storeManager->getStore($storeId)->getWebsiteId();
    }
}

<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Elgentos\AlternateUrls\Model\AlternateUrl;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Category extends AbstractType implements TypeInterface, ArgumentInterface
{
    private ?CategoryInterface $currentCategory = null;

    private Registry $registry;

    private CategoryRepositoryInterface $categoryRepository;

    private CategoryUrlPathGenerator $urlPathGenerator;

    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        CategoryUrlPathGenerator $urlPathGenerator
    ) {
        parent::__construct(
            $serializer,
            $scopeConfig,
            $storeManager,
            $request
        );

        $this->registry           = $registry;
        $this->categoryRepository = $categoryRepository;
        $this->urlPathGenerator   = $urlPathGenerator;
    }

    public function getAlternateUrls(): array
    {
        $currentCategory = $this->getCurrentCategory();

        if (!$currentCategory || !$currentCategory->getId()) {
            return [];
        }

        $result = [];

        foreach ($this->getMapping() as $item) {
            try {
                $result[] = new AlternateUrl(
                    $item['hreflang'],
                    $this->getCategoryUrl((int)$item['store_id'])
                );
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $result;
    }

    public function getCurrentCategory(): ?CategoryInterface
    {
        return $this->currentCategory ??= $this->registry->registry('current_category');
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getStoreCategory(
        int $storeId
    ): CategoryModel {
        $currentStore    = $this->storeManager->getStore();
        $currentCategory = $this->getCurrentCategory();

        /** @var CategoryModel $category */
        $category = $currentStore->getId() === $storeId
            ? $currentCategory
            : $this->categoryRepository->get(
                $currentCategory->getId(),
                $storeId
            );

        return $category;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getCategoryUrl(int $storeId): string
    {
        $category = $this->getStoreCategory($storeId);

        return $category->getStore()->getBaseUrl() .
            $this->urlPathGenerator->getUrlPathWithSuffix($category);
    }
}

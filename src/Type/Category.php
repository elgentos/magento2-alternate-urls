<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Type;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
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

    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($serializer, $scopeConfig, $storeManager, $request);

        $this->registry           = $registry;
        $this->categoryRepository = $categoryRepository;
    }

    public function getAlternateUrls(): array
    {
        $currentCategory = $this->getCurrentCategory();

        if (!$currentCategory || !$currentCategory->getId()) {
            return [];
        }

        $currentStore = $this->storeManager->getStore();
        $result       = [];

        foreach ($this->getMapping() as $item) {
            try {
                $category = $currentStore->getId() === $item['store_id']
                    ? $currentCategory
                    : $this->categoryRepository->get(
                        $currentCategory->getId(),
                        $item['store_id']
                    );

                $result[] = [
                    'hreflang' => $item['hreflang'],
                    'url' => $category->getUrl()
                ];
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $result;
    }

    public function getCurrentCategory(): ?CategoryInterface
    {
        if (!$this->currentCategory) {
            $this->currentCategory = $this->registry->registry('current_category');
        }

        return $this->currentCategory;
    }
}

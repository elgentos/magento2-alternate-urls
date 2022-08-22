<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Tests\Type;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Elgentos\AlternateUrls\Type\Category;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Type\Category
 */
class CategoryTest extends TestCase
{
    /**
     * @covers ::getAlternateUrls
     * @covers ::getCurrentCategory
     * @covers ::getStoreCategory
     * @covers ::getCategoryUrl
     * @covers ::__construct
     *
     * @dataProvider setAlternateUrlsDataProvider
     */
    public function testGetAlternateUrls(
        bool $hasRegisteredCategory,
        bool $willThrowCategoryException
    ): void {
        $subject = new Category(
            $this->createSerializerMock($hasRegisteredCategory),
            $this->createMock(ScopeConfigInterface::class),
            $this->createStoreManagerMock(),
            $this->createMock(Http::class),
            $this->createRegistryMock($hasRegisteredCategory),
            $this->createCategoryRepositoryMock($willThrowCategoryException),
            $this->createMock(CategoryUrlPathGenerator::class)
        );

        $subject->getAlternateUrls();
    }

    private function createCategoryInstance(
        bool $hasRegisteredCategory = true
    ): CategoryModel {
        $category = $this->createMock(CategoryModel::class);
        $category->expects(self::any())
            ->method('getId')
            ->willReturn($hasRegisteredCategory ? 1 : false);

        $category->expects(self::any())
            ->method('getUrl')
            ->willReturn('https://domain.com/category.html');

        $category->expects(self::any())
            ->method('getStore')
            ->willReturn($this->createStoreMock());

        return $category;
    }

    private function getMappingData(): array
    {
        return [
            ['hreflang' => 'nl', 'store_id' => 1],
            ['hreflang' => 'de', 'store_id' => 2],
            ['hreflang' => 'fr', 'store_id' => 3],
        ];
    }

    public function setAlternateUrlsDataProvider(): array
    {
        return [
            'withRegisteredCategory' => [true, false],
            'withoutRegisteredCategory' => [false, false],
            'willThrowCategoryException' => [true, true]
        ];
    }

    private function createRegistryMock(bool $hasRegisteredCategory): Registry
    {
        $registry = $this->createMock(Registry::class);
        $registry->expects(self::once())
            ->method('registry')
            ->willReturn($this->createCategoryInstance($hasRegisteredCategory));

        return $registry;
    }

    private function createSerializerMock(bool $hasRegisteredCategory): Json
    {
        $serializer = $this->createMock(Json::class);
        $serializer->expects($hasRegisteredCategory ? self::once() : self::never())
            ->method('unserialize')
            ->willReturn($this->getMappingData());

        return $serializer;
    }

    private function createStoreManagerMock(): StoreManagerInterface
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects(self::any())
            ->method('getStore')
            ->willReturn($this->createStoreMock());

        return $storeManager;
    }

    private function createCategoryRepositoryMock(bool $willThrowCategoryException): CategoryRepositoryInterface
    {
        $categoryRepository = $this->createMock(CategoryRepositoryInterface::class);

        if ($willThrowCategoryException) {
            $categoryRepository->expects(self::any())
                ->method('get')
                ->willThrowException(new NoSuchEntityException(__('Category not found')));
        } else {
            $categoryRepository->expects(self::any())
                ->method('get')
                ->willReturn($this->createCategoryInstance());
        }

        return $categoryRepository;
    }

    private function createStoreMock(): Store
    {
        $store = $this->createMock(Store::class);
        $store->expects(self::any())
            ->method('getId')
            ->willReturn(1);

        $store->expects(self::any())
            ->method('getBaseUrl')
            ->willReturn('https://domain.com/');

        return $store;
    }
}

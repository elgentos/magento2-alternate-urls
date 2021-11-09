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
     * @covers ::__construct
     *
     * @dataProvider setAlternateUrlsDataProvider
     */
    public function testGetAlternateUrls(
        bool $hasRegisteredCategory = true,
        bool $willThrowCategoryException = false
    ): void {
        $registry = $this->createMock(Registry::class);
        $registry->expects(self::once())
            ->method('registry')
            ->willReturn($this->createCategoryInstance($hasRegisteredCategory));

        $serializer = $this->createMock(Json::class);
        $serializer->expects($hasRegisteredCategory ? self::once() : self::never())
            ->method('unserialize')
            ->willReturn($this->getMappingData());

        $store = $this->createMock(Store::class);
        $store->expects(self::any())
            ->method('getId')
            ->willReturn(1);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects(self::any())
            ->method('getStore')
            ->willReturn($store);

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

        $subject = new Category(
            $serializer,
            $this->createMock(ScopeConfigInterface::class),
            $storeManager,
            $this->createMock(Http::class),
            $registry,
            $categoryRepository
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
            'withRegisteredCategory' => [true],
            'withoutRegisteredCategory' => [false],
            'willThrowCategoryException' => [true, true]
        ];
    }
}

<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Tests\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Elgentos\AlternateUrls\Type\Product;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Type\Product
 */
class ProductTest extends TestCase
{
    /**
     * @covers ::getStoreProduct
     * @covers ::getAlternateUrls
     * @covers ::getCurrentProduct
     * @covers ::__construct
     *
     * @dataProvider setAlternateUrlsDataProvider
     */
    public function testGetAlternateUrls(
        bool $hasRegisteredProduct = true,
        bool $willThrowProductException = false
    ): void {
        $registry = $this->createMock(Registry::class);
        $registry->expects(self::once())
            ->method('registry')
            ->willReturn($this->createProductInstance($hasRegisteredProduct));

        $serializer = $this->createMock(Json::class);
        $serializer->expects($hasRegisteredProduct ? self::once() : self::never())
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

        $productRepository = $this->createMock(ProductRepositoryInterface::class);

        if ($willThrowProductException) {
            $productRepository->expects(self::any())
                ->method('getById')
                ->willThrowException(new NoSuchEntityException(__('Category not found')));
        } else {
            $productRepository->expects(self::any())
                ->method('getById')
                ->willReturn($this->createProductInstance());
        }

        $subject = new Product(
            $serializer,
            $this->createMock(ScopeConfigInterface::class),
            $storeManager,
            $this->createMock(Http::class),
            $registry,
            $productRepository
        );
        $subject->getAlternateUrls();
    }

    private function createProductInstance(bool $hasRegisteredProduct = true): ProductModel
    {
        $product = $this->createMock(ProductModel::class);
        $product->expects(self::any())
            ->method('getId')
            ->willReturn($hasRegisteredProduct ? 1 : false);

        $product->expects(self::any())
            ->method('getProductUrl')
            ->willReturn('https://domain.com/product.html');

        return $product;
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

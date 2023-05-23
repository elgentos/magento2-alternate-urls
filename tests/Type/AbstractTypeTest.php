<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Tests\Type;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Elgentos\AlternateUrls\Type\AbstractType;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Type\AbstractType
 */
class AbstractTypeTest extends TestCase
{
    /**
     * @covers ::getMapping
     *
     * @dataProvider setMappingDataProvider
     */
    public function testGetMapping()
    {
        $serializer = $this->createMock(Json::class);
        $serializer->expects(self::once())
            ->method('unserialize')
            ->willReturn([]);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::once())
            ->method('getValue')
            ->willReturn('string');

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $request      = $this->createMock(Http::class);
        $subject      = $this->createAbstractTypeInstance(
            $serializer,
            $scopeConfig,
            $storeManager,
            $request
        );

        $this->assertIsArray($subject->getMapping());
    }

    /**
     * @covers ::getCurrentUrlWithoutParameters
     *
     * @dataProvider setCurrentUrlDataSet
     */
    public function testGetCurrentUrlWithoutParameters(
        string $storeUrl,
        string $requestString = ''
    ) {
        $request = $this->createMock(Http::class);
        $request->expects(self::once())
            ->method('getRequestString')
            ->willReturn($requestString);

        $subject = $this->createAbstractTypeInstance(
            $this->createMock(Json::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(StoreManagerInterface::class),
            $request
        );

        $store = $this->createMock(Store::class);
        $store->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn($storeUrl);

        $subject->getCurrentUrlWithoutParameters($store);
    }

    public function setMappingDataProvider(): array
    {
        return [
            'emptyMappingSet' => [[]],
            'filledMappingSet' => [
                [
                    ['item_id' => 1],
                    ['item_id' => 2],
                    ['item_id' => 3]
                ]
            ],
        ];
    }

    private function createAbstractTypeInstance(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Http $request
    ): AbstractType {
        return new class ($serializer, $scopeConfig, $storeManager, $request) extends AbstractType {
        };
    }

    public function setCurrentUrlDataSet(): array
    {
        return [
            'invalidUrl' => [''],
            'validUrl' => ['https://www.domain.com/', '/custom-path'],
        ];
    }
}

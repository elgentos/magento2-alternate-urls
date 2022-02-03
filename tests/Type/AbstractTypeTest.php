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
        $subject = $this->createAbstractTypeInstance(
            $this->createSerializerMock(),
            $this->createScopeConfigMock(),
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(Http::class)
        );

        $this->assertIsArray($subject->getMapping());
    }

    public function testModifyUrl()
    {
        $subject = $this->createAbstractTypeInstance(
            $this->createSerializerMock(false),
            $this->createScopeConfigMock(false),
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(Http::class)
        );

        $subject->modifyUrl('https://www.google.com');
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
            $this->createSerializerMock(false),
            $this->createScopeConfigMock(false),
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

    private function createSerializerMock(bool $isCalled = true): Json
    {
        $serializer = $this->createMock(Json::class);
        $serializer->expects($isCalled ? self::once() : self::never())
            ->method('unserialize')
            ->willReturn([]);

        return $serializer;
    }

    private function createScopeConfigMock(
        bool $hasValue = true
    ): ScopeConfigInterface {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($hasValue ? self::once() : self::never())
            ->method('getValue')
            ->willReturn('string');

        $scopeConfig->expects(self::any())
            ->method('isSetFlag')
            ->willReturn(true);

        return $scopeConfig;
    }
}

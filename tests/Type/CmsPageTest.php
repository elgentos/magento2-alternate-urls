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
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Elgentos\AlternateUrls\Type\CmsPage;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Type\CmsPage
 */
class CmsPageTest extends TestCase
{
    public function testGetAlternateUrls(): void
    {
        $serializer = $this->createMock(Json::class);
        $serializer->expects(self::once())
            ->method('unserialize')
            ->willReturn($this->getMappingData());

        $store = $this->createMock(Store::class);
        $store->expects(self::any())
            ->method('getId')
            ->willReturn(1);

        $store->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn('https://domain.com/');

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects(self::once())
            ->method('getStore')
            ->willReturn($store);

        $request = $this->createMock(Http::class);
        $request->expects(self::once())
            ->method('getRequestString')
            ->willReturn('/custom/path');

        $request->expects(self::any())
            ->method('getQueryValue')
            ->willReturn(['foo' => 'bar']);

        $subject = new CmsPage(
            $serializer,
            $this->createMock(ScopeConfigInterface::class),
            $storeManager,
            $request
        );
        $subject->getAlternateUrls();
    }

    private function getMappingData(): array
    {
        return [
            ['hreflang' => 'nl', 'store_id' => 1],
            ['hreflang' => 'de', 'store_id' => 2],
            ['hreflang' => 'fr', 'store_id' => 3],
        ];
    }
}

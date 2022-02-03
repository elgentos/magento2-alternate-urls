<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Tests\Block;

use Elgentos\AlternateUrls\Block\AlternateUrls;
use Elgentos\AlternateUrls\Type\TypeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Block\AlternateUrls
 */
class AlternateUrlsTest extends TestCase
{
    /**
     * @dataProvider setDataProvider
     */
    public function testGetTypeInstance(
        bool $isEnabled = true,
        array $data = []
    ) {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::once())
            ->method('isSetFlag')
            ->willReturn($isEnabled);

        $request = $this->createMock(Http ::class);
        $request->expects(self::any())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $context = $this->createMock(Context::class);
        $context->expects(self::any())
            ->method('getRequest')
            ->willReturn($request);

        $subject = new AlternateUrls(
            $context,
            $scopeConfig,
            $data
        );

        $subject->getTypeInstance();
    }

    public function setDataProvider(): array
    {
        return [
            'disabled' => [false],
            'enabledWithoutData' => [true, []],
            'enabledWithoutInstances' => [true, ['typeInstances' => []]],
            'enabledWithOnlyDefaultInstance' => [
                true,
                [
                    'typeInstances' => [
                        'default' => $this->createMock(TypeInterface::class),
                    ]
                ]
            ],
            'enabledWithInstances' => [
                true,
                [
                    'typeInstances' => [
                        'default' => $this->createMock(TypeInterface::class),
                        'catalog_product_view' => $this->createMock(TypeInterface::class)
                    ]
                ]
            ],
        ];
    }
}

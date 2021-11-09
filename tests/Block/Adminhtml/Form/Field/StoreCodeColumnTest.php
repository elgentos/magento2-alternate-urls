<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Tests\Block\Adminhtml\Form\Field;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;
use Elgentos\AlternateUrls\Block\Adminhtml\Form\Field\StoreCodeColumn;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Block\Adminhtml\Form\Field\StoreCodeColumn
 */
class StoreCodeColumnTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::setInputId
     */
    public function testSetInputId(): void
    {
        $inputId = 'foobar_id';
        $subject = new StoreCodeColumn(
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(Context::class)
        );

        $subject->setInputId($inputId);

        $this->assertEquals($inputId, $subject->getId());
    }

    /**
     * @covers ::__construct
     * @covers ::setInputName
     */
    public function testSetInputName(): void
    {
        $inputName = 'foobar';
        $subject   = new StoreCodeColumn(
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(Context::class)
        );

        $subject->setInputName($inputName);

        $this->assertEquals($inputName, $subject->getData('name'));
    }

    /**
     * @covers ::__construct
     * @covers ::_toHtml
     * @covers ::getSourceOptions
     */
    public function testToHtml(): void
    {
        $storesManager = $this->createMock(StoreManagerInterface::class);
        $storesManager->expects(self::once())
            ->method('getStores')
            ->willReturn($this->createStoresArray());

        $context = $this->createMock(Context::class);
        $context->expects(self::once())
            ->method('getEscaper')
            ->willReturn($this->createMock(Escaper::class));

        $subject = new StoreCodeColumn(
            $storesManager,
            $context
        );

        $subject->_toHtml();
    }

    private function createStoresArray(): array
    {
        $counter = 3;
        $stores  = [];
        $website = $this->createMock(Website::class);
        $website->expects(self::any())
            ->method('getName')
            ->willReturn('Website Name');

        $store = $this->createMock(Store::class);
        $store->expects(self::any())
            ->method('getName')
            ->willReturn('Store Name');

        $store->expects(self::any())
            ->method('getWebsite')
            ->willReturn($website);

        $store->expects(self::any())
            ->method('getCode')
            ->willReturn('Store Code');

        $store->expects(self::any())
            ->method('getId')
            ->willReturn(1);

        for ($i = 0; $i < $counter; $i++) {
            $stores[] = $store;
        }

        return $stores;
    }
}

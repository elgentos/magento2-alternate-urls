<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Tests\Block\Adminhtml\Form\Field;

use Elgentos\AlternateUrls\Block\Adminhtml\Form\Field\AlternateUrls;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Block\Adminhtml\Form\Field\AlternateUrls
 */
class AlternateUrlsTest extends TestCase
{
    public function setUp(): void
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        ObjectManager::setInstance($objectManagerMock);
    }

    /**
     * @covers ::_prepareToRender
     * @covers ::getStoreCodeRenderer
     */
    public function testPrepareToRender(): void
    {
        $subject = new AlternateUrls(
            $this->createMock(Context::class),
            [],
            $this->createMock(SecureHtmlRenderer::class)
        );

        $subject->setLayout($this->initializeLayout());

        $this->assertCount(0, $subject->getColumns());

        $reflectionMethod = new ReflectionMethod($subject, '_prepareToRender');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($subject);

        $this->assertCount(2, $subject->getColumns());
    }

    /**
     * @covers ::_prepareArrayRow
     * @covers ::getStoreCodeRenderer
     */
    public function testPrepareArrayRow(): void
    {
        $subject = new AlternateUrls(
            $this->createMock(Context::class),
            [],
            $this->createMock(SecureHtmlRenderer::class)
        );

        $subject->setLayout($this->initializeLayout());

        $row = $this->createMock(DataObject::class);
        $row->expects(self::once())
            ->method('getData')
            ->with('store_id')
            ->willReturn(1);

        $reflectionMethod = new ReflectionMethod($subject, '_prepareArrayRow');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(
            $subject,
            $row
        );
    }

    /**
     * @return LayoutInterface
     */
    private function initializeLayout(): LayoutInterface
    {
        $layout = $this->createMock(LayoutInterface::class);
        $layout->expects(self::any())
            ->method('createBlock')
            ->willReturn($this->createMock(Select::class));

        return $layout;
    }
}

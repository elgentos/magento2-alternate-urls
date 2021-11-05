<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Html\Select;

class AlternateUrls extends AbstractFieldArray
{
    /**
     * @var ?BlockInterface
     */
    private ?BlockInterface $storeCodeRenderer = null;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'hreflang',
            [
                'label' => __('Language Code')
            ]
        );

        $this->addColumn(
            'store_id',
            [
                'label' => __('Store'),
                'renderer' => $this->getStoreCodeRenderer()
            ]
        );

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add Row')->getText();
    }

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    private function getStoreCodeRenderer(): BlockInterface
    {
        if (!$this->storeCodeRenderer) {
            $this->storeCodeRenderer = $this->getLayout()->createBlock(
                StoreCodeColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->storeCodeRenderer;
    }

    /**
     * @param DataObject $row
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $storeId = $row->getData('store_id');
        if ($storeId !== null) {
            /** @var Select $storeCodeRenderer */
            $storeCodeRenderer = $this->getStoreCodeRenderer();

            $options['option_' . $storeCodeRenderer->calcOptionHash($storeId)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}

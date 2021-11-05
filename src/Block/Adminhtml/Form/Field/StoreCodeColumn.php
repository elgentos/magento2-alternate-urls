<?php

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Block\Adminhtml\Form\Field;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class StoreCodeColumn extends Select
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Context               $context
     * @param array                 $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $value
     *
     * @return Select
     */
    public function setInputId(string $value): Select
    {
        return $this->setId($value);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     *
     * @return $this
     */
    public function setInputName(string $value): StoreCodeColumn
    {
        return $this->setData('name', $value);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        return parent::_toHtml();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSourceOptions(): array
    {
        $stores = $this->storeManager->getStores();

        $optionArray = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $website = $store->getWebsite();

            $optionArray[] = [
                'name' => 'boe',
                'label' =>
                    $website->getName() .
                    ' - ' .
                    $store->getName(),
                'value' => $store->getId()
            ];
        }

        return $optionArray;
    }
}

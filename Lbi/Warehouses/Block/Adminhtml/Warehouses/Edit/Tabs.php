<?php
namespace Lbi\Warehouses\Block\Adminhtml\Warehouses\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('warehouses_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Warehouse Information'));
    }
	
    protected function _beforeToHtml()
    {
        $this->addTab(
            'warehouses_info',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'Lbi\Warehouses\Block\Adminhtml\Warehouses\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
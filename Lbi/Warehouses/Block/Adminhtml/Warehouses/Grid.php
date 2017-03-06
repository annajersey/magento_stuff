<?php
namespace Lbi\Warehouses\Block\Adminhtml\Warehouses;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Lbi_Warehouses';
        $this->_controller = 'adminhtml_warehouses';
        $this->_headerText = __('Warehouses');
        $this->_addButtonLabel = __('Add New Warehouse');
        parent::_construct();
    }
}
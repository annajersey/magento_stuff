<?php
namespace Lbi\Warehouses\Controller\Adminhtml\Warehouses;

class NewAction extends Warehouses
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
<?php
namespace Lbi\Warehouses\Model;

use Magento\Framework\Model\AbstractModel;

class Warehouses extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Lbi\Warehouses\Model\Resource\Warehouses');
    }
}
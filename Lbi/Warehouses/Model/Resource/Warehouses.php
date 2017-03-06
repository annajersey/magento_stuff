<?php
namespace Lbi\Warehouses\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Warehouses extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('lbi_warehouses', 'id');
    }
}
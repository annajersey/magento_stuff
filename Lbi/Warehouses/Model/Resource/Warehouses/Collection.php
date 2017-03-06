<?php
namespace Lbi\Warehouses\Model\Resource\Warehouses;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Lbi\Warehouses\Model\Warehouses', 'Lbi\Warehouses\Model\Resource\Warehouses');
    }
}
<?php
 
namespace Lbi\Ylprod\Model\Resource\ProductNeedRelations;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	public function _construct()
	{
		$this->_init(
			'Lbi\Ylprod\Model\ProductNeedRelations',
			'Lbi\Ylprod\Model\Resource\ProductNeedRelations'
		);
	}
}
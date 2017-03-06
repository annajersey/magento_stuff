<?php
 
namespace Lbi\Ylprod\Model\Resource;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class ProductNeedRelations extends AbstractDb
{
	protected function _construct()
	{
		$this->_init('lbi_ylprod_need_relations', 'id');
	}
}
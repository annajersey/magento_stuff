<?php
 
namespace Lbi\Ylprod\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class ProductNeedRelations extends AbstractModel
{
	protected function _construct()
	{
		$this->_init('Lbi\Ylprod\Model\Resource\ProductNeedRelations');
	}
}
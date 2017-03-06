<?php
namespace Lbi\Spec\Block;
use Magento\Framework\View\Element\Template;

class Sale extends \Magento\Catalog\Block\Product\ListProduct
{    
    protected function _prepareLayout()
    {
    
    }
	
	public function getLoadedProductCollection()
    {
        //$collection = parent::_getProductCollection();
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        
        $collection      = $objectManager->create(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection'
        );
        $collection      = $this->_addProductAttributesAndPrices($collection)
            ->addAttributeToFilter(
                'special_price',
                ['gt'=>0], 'left'
			)->addAttributeToFilter(
                'special_from_date',['or' => [ 0 => ['date' => true, 
													'to' => $this->getEndOfDayDate()],
											  1 => ['is' => new \Zend_Db_Expr(
                                                 'null'
                                             )],]], 'left'
            )->addAttributeToFilter(
                'special_to_date',  ['or' => [ 0 => ['date' => true,
                                                   'from' => $this->getStartOfDayDate()],
                                             1 => ['is' => new \Zend_Db_Expr(
                                                 'null'
                                             )],]], 'left'
            )->addAttributeToSort(
                'news_from_date', 'desc'
            );
		
		$ids=array();
		foreach($collection as $product){
			$productType = $product->getTypeID();
			if($productType == 'simple')
			{   
			 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$parent = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getId());
				 if(isset($parent[0])){
					 $ids[]=$parent[0];
				}else{
					$ids[]=$product->getID();
				}
			}else{
				$ids[]=$product->getID();
			}	
			
		}
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $visibleProducts = $objectManager->create(
            '\Magento\Catalog\Model\Product\Visibility'
        )->getVisibleInCatalogIds();
        $new_collection = $objectManager->create(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection'
        )->setVisibility($visibleProducts);
		$new_collection   = $this->_addProductAttributesAndPrices($new_collection);
		$stockFilter = $objectManager->create('\Magento\CatalogInventory\Helper\Stock');
		$stockFilter->addInStockFilterToCollection($new_collection);
		
		$new_collection->addFieldToFilter('entity_id', array('in' =>  $ids));
		//echo $new_collection->getSelect()->assemble();
        return $new_collection;
    }
	
	public function getEndOfDayDate()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		return $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date(null, '23:59:59');
	}
	
	public function getStartOfDayDate()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		return $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date(null, '0:0:0');
	}
	

    
}

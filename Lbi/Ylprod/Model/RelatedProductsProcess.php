<?php
 
namespace Lbi\Ylprod\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class RelatedProductsProcess extends AbstractModel
{

	private $_productFactoryModel;
	private $_productLinkType;
	private $_productLinkResourceModel;

	public function __construct(
		\Magento\Catalog\Model\Factory $productFactoryModel,
		\Magento\Catalog\Model\Product\Link $productLinkModel,
		\Magento\Catalog\Model\ResourceModel\Product\Link $productLinkResourceModel
	) {
		$this->_productFactoryModel = $productFactoryModel;
		$this->_productLinkType = $productLinkModel::LINK_TYPE_RELATED;
		$this->_productLinkResourceModel = $productLinkResourceModel;
	}
	
	public function assign($productIds = [], $needRelatedProductIds = [])
	{
		if (!empty($productIds) && !empty($needRelatedProductIds)) {
			foreach ($productIds as $productId) {
				$insertData = [];
				$product = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($productId);
				if (!empty($product) && (int) $product->getId() > 0) {
					$currentRelatedProductIds = [];
					$relatedProductCollection = $product->getRelatedProductCollection();
					if ($relatedProductCollection->count()) {
						foreach ($relatedProductCollection as $currentRelatedProduct) {
							$currentRelatedProductIds[] = $currentRelatedProduct->getId();
						}
					}
					$relatedLinkCollection = $product->getRelatedLinkCollection();
					if ($relatedLinkCollection->count()) {
						foreach ($relatedLinkCollection as $relatedLink) {
							if (!empty($link = $relatedLink->getData())) {
								$position = '';
								$qty = '';
								if (!empty($link['position'])) {
									$position = $link['position'];
								}
								if (!empty($link['qty'])) {
									$qty = $link['qty'];
								}
								$insertData[$link['linked_product_id']] = array('position' => $position, 'qty' => $qty) ;
							}
						}
					}
					foreach ($needRelatedProductIds as $needRelatedProductId) {
						if (($productId == $needRelatedProductId) || (!empty($currentRelatedProductIds) && in_array($needRelatedProductId, $currentRelatedProductIds)) || (int) $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($needRelatedProductId)->getId() <= 0) {
							continue;
						}
						$insertData[$needRelatedProductId] = array('position' => '', 'qty' => '') ;
					}
					if (count($insertData) > count($currentRelatedProductIds)) {
						$this->_productLinkResourceModel->saveProductLinks($product, $insertData, $this->_productLinkType);
					}
				}
			}
		}
		return true;
	}
}
<?php

namespace Lbi\Ylprod\Cron;
 
class ProductNeedRelations {

	private $_productFactoryModel;
	private $_productNeedRelationsModel;
	private $_relatedProductsProcessModel;
 
    public function __construct(
		\Magento\Catalog\Model\Factory $productFactoryModel,
		\Lbi\Ylprod\Model\ProductNeedRelationsFactory $productNeedRelationsModel,
		\Lbi\Ylprod\Model\RelatedProductsProcess $relatedProductsProcessModel
    ) {
		$this->_productFactoryModel = $productFactoryModel;
		$this->_productNeedRelationsModel = $productNeedRelationsModel;
		$this->_relatedProductsProcessModel = $relatedProductsProcessModel;
    }

    public function execute() {
		$productNeedRelationsCollection = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\Resource\ProductNeedRelations\Collection');
		$query = $productNeedRelationsCollection->getSelect()->columns('GROUP_CONCAT(DISTINCT product_id SEPARATOR ",") as ids')->where('status = 0')->group('product_code')->__toString();
		$codeItems = $productNeedRelationsCollection->getConnection()->fetchAll($query);
		if (!empty($codeItems)) {
			foreach ($codeItems as $codeItem) {
				$productIds = explode(',', $codeItem['ids']);
				$needRelatedProductIds = [];
				$productCode = $codeItem['product_code'];
				$needRelatedProductIds = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('product_code', array('eq' => $productCode))->addAttributeToFilter('status', array('in' => array(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED, \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)))->getAllIds();
				if (count($productIds) && count($needRelatedProductIds)) {
					$this->_relatedProductsProcessModel->assign($productIds, $needRelatedProductIds);
					$this->_relatedProductsProcessModel->assign($needRelatedProductIds, $productIds);
					foreach ($productIds as $productId) {
						$productNeedRelationsCollection = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\Resource\ProductNeedRelations\Collection')->addFieldToFilter('product_id', array('eq' => $productId))->getData();
						if (!empty($productNeedRelationsCollection[0])) {
							$productNeedRelations = $productNeedRelationsCollection[0];
							$productNeedRelationsUpdate = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\ProductNeedRelations')->load($productNeedRelations['id']);
							$productNeedRelationsUpdate->setStatus(1);
							$productNeedRelationsUpdate->save();
						}
					}
				}
			}
		}
        return $this;
    }
}
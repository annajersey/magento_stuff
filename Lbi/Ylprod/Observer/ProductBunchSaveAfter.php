<?php
 
namespace Lbi\Ylprod\Observer;
 
use Magento\Framework\Event\ObserverInterface;

class ProductBunchSaveAfter implements ObserverInterface
{

	private $_productRepositoryInterface;
	private $_productNeedRelationsModel;

	public function __construct(
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface, 
		\Lbi\Ylprod\Model\ProductNeedRelationsFactory $productNeedRelationsModel
	) {
		$this->_productRepositoryInterface = $productRepositoryInterface;
		$this->_productNeedRelationsModel = $productNeedRelationsModel;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$bunch = $observer->getEvent()->getBunch();
		if (!empty($bunch) && is_array($bunch)) {
			foreach ($bunch as $line) {
				if (!empty($line) && is_array($line)) {
					foreach ($line as $column => $value) {
						if ($column == 'sku' && !empty($value)) {
							$sku = $value;
							$product = $this->_productRepositoryInterface->get($sku);
							if (!empty($product) && (int) $product->getId() > 0) {
								$productCode = $product->getData('product_code');
								$mainProductId = $product->getId();
								if (!empty($productCode)) {
									$productNeedRelationsCollection = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\Resource\ProductNeedRelations\Collection')->addFieldToFilter('product_id', array('eq' => $mainProductId))->getData();
									if (!empty($productNeedRelationsCollection[0])) {
										$productNeedRelations = $productNeedRelationsCollection[0];
										if ($productNeedRelations['product_code'] != $productCode) {
											$productNeedRelationsUpdate = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\ProductNeedRelations')->load($productNeedRelations['id']);
											$productNeedRelationsUpdate->setProductCode($productCode); 
											$productNeedRelationsUpdate->setStatus(0);
											$productNeedRelationsUpdate->save();
										}
									} else {
										$productNeedRelations = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\ProductNeedRelations');
										$productNeedRelations->setProductId($mainProductId);
										$productNeedRelations->setProductCode($productCode);
										$productNeedRelations->setStatus(0);
										$productNeedRelations->save();
									}
								} else {
									$productNeedRelationsCollection = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\Resource\ProductNeedRelations\Collection')->addFieldToFilter('product_id', array('eq' => $mainProductId))->getData();
									if (!empty($productNeedRelationsCollection[0])) {
										$productNeedRelations = $productNeedRelationsCollection[0];
										if (!empty($productNeedRelations['product_code'])) {
											$productNeedRelationsUpdate = $this->_productNeedRelationsModel->create('Lbi\Ylprod\Model\ProductNeedRelations')->load($productNeedRelations['id']);
											$productNeedRelationsUpdate->setProductCode(''); 
											$productNeedRelationsUpdate->setStatus(1);
											$productNeedRelationsUpdate->save();
										}
									}
								}
							}
							break;
						}
					}
				}
			}
		}
    }
}
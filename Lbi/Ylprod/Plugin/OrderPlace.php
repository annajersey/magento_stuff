<?php

namespace Lbi\Ylprod\Plugin;

use \Magento\Sales\Api\Data\OrderInterface;

class OrderPlace {
	
	private $_disabledParents = [];
	private $_disabledCodes = [];
	private $_stockState;
	private $_configurableProductModel;
	private $_productFactoryModel;
	private $_groupedProductModel;
	private $_bundleProductModel;
	private $_productCollection;
	
	public function __construct(
		\Magento\CatalogInventory\Api\StockStateInterface $stockState,
		\Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductModel,
		\Magento\Catalog\Model\Factory $productFactoryModel,
		\Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProductModel,
		\Magento\Bundle\Model\Product\Type $bundleProductModel,
		\Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
	) {
		$this->_stockState = $stockState;
		$this->_configurableProductModel = $configurableProductModel;
		$this->_productFactoryModel = $productFactoryModel;
		$this->_groupedProductModel = $groupedProductModel;
		$this->_bundleProductModel = $bundleProductModel;
		$this->_productCollection = $productCollection;
	}
	
	private function _processCheckNeedDisableByProductCode($productId, $minimumProductItemQty)
	{
		$product = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($productId);
		$type = $product->getTypeId();
		if (!empty($type)) {
			switch ($type) {
				case 'grouped':
					$groupedChildrenIds = $this->_groupedProductModel->getChildrenIds($productId);
					if (!empty($groupedChildrenIds)) {
						foreach ($groupedChildrenIds as $groupedChildIds) {
							if (!empty($groupedChildIds)) {
								foreach ($groupedChildIds as $groupedChildId) {
									$groupedChildProduct = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($groupedChildId);
									$stockQty = (float) $this->_stockState->getStockQty($groupedChildId, $groupedChildProduct->getStore()->getWebsiteId());
									if ($stockQty <= $minimumProductItemQty) {
										return true;
									}
								}
							}
						}
					}
					break;
				case 'bundle':
					$bundledChildrenIds = $this->_bundleProductModel->getChildrenIds($productId);
					if (!empty($bundledChildrenIds)) {
						foreach ($bundledChildrenIds as $bundledChildIds) {
							if (!empty($bundledChildIds)) {
								foreach ($bundledChildIds as $bundledChildId) {
									$bundledChildProduct = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($bundledChildId);
									$stockQty = (float) $this->_stockState->getStockQty($bundledChildId, $bundledChildProduct->getStore()->getWebsiteId());
									if ($stockQty <= $minimumProductItemQty) {
										return true;
									}
								}
							}
						}
					}
					break;
				case 'configurable':
					$configurableChildIds = $this->_configurableProductModel->getUsedProductIds($product);
					if (!empty($configurableChildIds)) {
						foreach ($configurableChildIds as $configurableChildId) {
							$configurableChildProduct = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($configurableChildId);
							$stockQty = (float) $this->_stockState->getStockQty($configurableChildId, $configurableChildProduct->getStore()->getWebsiteId());
							if ($stockQty <= $minimumProductItemQty) {
								return true;
							}
						}
					}
					break;
			}
		}
		return false;
	}
	
	private function _checkNeedDisableByProductCode($productCode, $minimumProductItemQty)
	{
		$productIds = $this->_productCollection->addAttributeToSelect('*')->addAttributeToFilter('product_code', array('eq' => $productCode))->addAttributeToFilter('type_id', array('in' => array('grouped','bundle','configurable')))->addAttributeToFilter('status', array('in' => array(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED, \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)))->getAllIds();
		if (count($productIds)) {
			foreach ($productIds as $productId) {
				if (!in_array($productId, $this->_disabledParents)) {
					if ($this->_processCheckNeedDisableByProductCode($productId, $minimumProductItemQty)) {
						$this->_processDisableByProductCode($productCode);
						return true;
					}
				}
			}
		}
		return true;
	}
	
	private function _processDisableByProductCode($productCode)
	{
		$this->_disabledCodes[] = $productCode;
		$productIds = $this->_productCollection->addAttributeToSelect('*')->addAttributeToFilter('product_code', array('eq' => $productCode))->addAttributeToFilter('type_id', array('in' => array('grouped','bundle','configurable')))->addAttributeToFilter('status', array('in' => array(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED, \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)))->getAllIds();
		if (count($productIds)) {
			foreach ($productIds as $productId) {
				if (!in_array($productId, $this->_disabledParents)) {
					$product = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($productId);
					$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
					$product->save();
					$this->_disabledParents[] = $productId;
				}
			}
		}
		return true;
	}
	
	private function _disableByProductCode($productCode, $productId, $minimumProductItemQty)
	{
		if (in_array($productId, $this->_disabledParents)) {
			$this->_processDisableByProductCode($productCode);			
		} else {
			$this->_checkNeedDisableByProductCode($productCode, $minimumProductItemQty);
		}
		return true;
	}
	
	private function _processParentIds($parentIds, $orderProduct)
	{
		foreach ($parentIds as $parentId) {
			if (!in_array($parentId, $this->_disabledParents)) {
				$parentProduct = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($parentId);
				$productCode = $parentProduct->getData('product_code');
				$minimumProductItemQty = (float) $parentProduct->getData('minimum_product_item_qty');
				$stockQty = (float) $this->_stockState->getStockQty($orderProduct->getId(), $orderProduct->getStore()->getWebsiteId());
				if (!empty($minimumProductItemQty)) {
					if ($stockQty <= $minimumProductItemQty) {
						$parentProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
						$parentProduct->save();
						$this->_disabledParents[] = $parentId;
					}
					if (!empty($productCode) && !in_array($productCode, $this->_disabledCodes)) {
						$this->_disableByProductCode($productCode, $parentId, $minimumProductItemQty);
					}
				}
			}
		}
		return true;
	}
	
	private function _processChildIds($childIds, $parentProduct)
	{
		if (!in_array($parentProduct->getId(), $this->_disabledParents)) {
			$productCode = $parentProduct->getData('product_code');
			$minimumProductItemQty = (float) $parentProduct->getData('minimum_product_item_qty');
			foreach ($childIds as $childId) {
				$childProduct = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($childId);
				$stockQty = (float) $this->_stockState->getStockQty($childId, $childProduct->getStore()->getWebsiteId());
				if (!empty($minimumProductItemQty) && $stockQty <= $minimumProductItemQty) {
					$parentProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
					$parentProduct->save();
					$this->_disabledParents[] = $parentProduct->getId();
					break;
				}
			}
			if (!empty($productCode) && !in_array($productCode, $this->_disabledCodes)) {
				$this->_disableByProductCode($productCode, $parentProduct->getId(), $minimumProductItemQty);
			}
		}
		return true;
	}
	
	public function afterPlace(\Magento\Sales\Model\Service\OrderService\Interceptor $orderInterface, $order)
	{
		$orderItems = $order->getAllItems();
		if (count($orderItems)) {
			foreach ($orderItems as $orderItem) {
				$groupedParentIds = $this->_groupedProductModel->getParentIdsByChild($orderItem->getProduct()->getId());
				if (!empty($groupedParentIds)) {
					$this->_processParentIds($groupedParentIds, $orderItem->getProduct());
				}
				$bundledParentIds = $this->_bundleProductModel->getParentIdsByChild($orderItem->getProduct()->getId());
				if (!empty($bundledParentIds)) {
					$this->_processParentIds($bundledParentIds, $orderItem->getProduct());
				}
				$configurableChildIds = $this->_configurableProductModel->getUsedProductIds($orderItem->getProduct());
				if (!empty($configurableChildIds)) {
					$this->_processChildIds($configurableChildIds, $orderItem->getProduct());
				}
			}
		}
		return $order;
	}
}
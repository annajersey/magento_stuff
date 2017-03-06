<?php
namespace Lbi\Design\Observer;

use Magento\Framework\Event\ObserverInterface;


class OrderShipmentSaveBefore implements ObserverInterface
{
   
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      
        $shipment = $observer->getEvent()->getShipment();
        $items = $shipment->getItems();
		$result = 0;
		foreach($items as $item){
			$order_item = $item->getOrderItem();
			$item_cost=$order_item->getPriceInclTax()*$item->getQty();
			$result+=$item_cost;
		}
		$shipment->setData('shipment_cost',$result);
    }

}
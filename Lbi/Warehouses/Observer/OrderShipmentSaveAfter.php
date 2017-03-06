<?php
namespace Lbi\Warehouses\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Factory as ProductFactoryModel;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;

class OrderShipmentSaveAfter implements ObserverInterface
{
    protected $_productFactoryModel;
    protected $_invoiceService;
    protected $_transaction;
    protected $_creditmemoFactory;
    protected $_creditmemoService;
    protected $_creditmemoCommentSender;

    public function __construct(
        ProductFactoryModel $productFactoryModel,
        InvoiceService $invoiceService,
        Transaction $transaction,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoService $creditmemoService,
        CreditmemoCommentSender $creditmemoCommentSender
    ) {
        $this->_productFactoryModel = $productFactoryModel;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_creditmemoFactory = $creditmemoFactory;
        $this->_creditmemoService = $creditmemoService;
		$this->_creditmemoCommentSender = $creditmemoCommentSender;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $unshippedItems = [];
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $orderShipmentsCount = $shipment->getOrder()->getShipmentsCollection()->count();
		
        foreach ($order->getAllStatusHistory() as $orderComment) {
            if ($orderComment->getStatus() == 'not_shipped_store_1') {
                $orderShipmentsCount = 2;
				break;
            }
        }

        foreach ($order->getAllItems() as $item) { 
            if ($item->getProductType() == 'configurable' || $item->getProductType() == 'downloadable' || $item->getProductType() == 'bundle' || $item->getProductType() == 'virtual' || $item->getLockedDoShip()) {
                continue;		
            }

            if ($itemQty = $this->_checkQtyToShip($item)) {
                $unshippedItems[$item->getId()] = $itemQty;
            }
        }

        if (!empty($unshippedItems)) {
            if ($orderShipmentsCount == 1) {
                $state = 'processing';
                $status = 'partially_shipped_store_1';
            } elseif ($orderShipmentsCount == 2) {
                $this->_refundUnshippedItems($order, $unshippedItems);
                $state = 'complete';
                $status = 'multi_store_shipment_partial';
            }
        } else {
            if ($orderShipmentsCount == 1) {
                $state = 'complete';
                $status = 'shipped_complete_store_1';
            } elseif ($orderShipmentsCount == 2) {
                $state = 'complete';
                $status = 'shipped_complete_store_2';
            } elseif ($orderShipmentsCount > 2) {
                $state = 'complete';
                $status = 'multi_store_shipment_complete';
            }
        }

        if (!empty($state) && !empty($status)) {
            $order->setState($state);
            $order->setStatus($status);
            $order->save();
        }
    }

    protected function _refundUnshippedItems($order, $unshippedItems)
    {
        if ($order->canInvoice()) {
            $invoice = $this->_invoiceService->prepareInvoice($order);
            if (!$invoice->getTotalQty()) {
                return false;
            }
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $transaction = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transaction->save();
			$creditmemo = $this->_creditmemoFactory->createByInvoice($invoice);
            $commentText = count($unshippedItems) . __(' item(s) has been refunded for order #') . $order->getId();
            $creditmemo->addComment($commentText, true);
			$this->_creditmemoService->refund($creditmemo, false);
            $orderStatusHistory = $order->getAllStatusHistory();
            if (!empty($orderStatusHistory[0])) {
                $orderComment = $orderStatusHistory[0];
                $refundComment = $orderComment->getComment();
                if (!empty($refundComment)) {
                    $commentText .= '. ' . $refundComment;
                }
            }
			$this->_creditmemoCommentSender->send($creditmemo, true, $commentText);
        } else {
            $creditmemo = $this->_creditmemoFactory->createByOrder($order, ['qtys' => $unshippedItems, 'shipping_amount' => 0]);
            $commentText = count($unshippedItems) . __(' item(s) has been refunded for order #') . $order->getId();
            $creditmemo->addComment($commentText, true);
			$this->_creditmemoService->refund($creditmemo, false);
            $orderStatusHistory = $order->getAllStatusHistory();
            if (!empty($orderStatusHistory[0])) {
                $orderComment = $orderStatusHistory[0];
                $refundComment = $orderComment->getComment();
                if (!empty($refundComment)) {
                    $commentText .= '. ' . $refundComment;
                }
            }
			$this->_creditmemoCommentSender->send($creditmemo, true, $commentText);
        }
        return true;
    }

    protected function _checkQtyToShip($item)
    {
		$qty = 0;
        $productId = (int) $item->getProductId();
        $_product = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($productId);
        if (!empty($_product)) {
            if (!empty($item->getParentItem())) {
                $item = $item->getParentItem();
            }
            $qty = $item->getQtyOrdered() - $item->getQtyShipped() - $item->getQtyRefunded() - $item->getQtyCanceled();
        }
        if ($qty > 0) {
            return max($qty, 0);
        }
        return false;
    }
}
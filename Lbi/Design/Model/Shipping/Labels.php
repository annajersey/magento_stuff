<?php
   
   namespace Lbi\Design\Model\Shipping;
   
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Address;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Store\Model\ScopeInterface;
use Magento\User\Model\User;
   class Labels extends \Magento\Shipping\Model\Shipping\Labels
   {
	/**
     * Set shipper details into request
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param \Magento\User\Model\User $storeAdmin
     * @param \Magento\Framework\DataObject $store
     * @param $shipmentStoreId
     * @param $regionCode
     * @param $originStreet
     * @return void
     */
    protected function setShipperDetails(
        Request $request,
        User $storeAdmin,
        DataObject $store,
        $shipmentStoreId,
        $regionCode,
        $originStreet
    ) {
        $originStreet2 = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS2,
            ScopeInterface::SCOPE_STORE,
            $shipmentStoreId
        );

        $request->setShipperContactPersonName('-');
        $request->setShipperContactPersonFirstName($storeAdmin->getFirstname());
        $request->setShipperContactPersonLastName($storeAdmin->getLastname());
        $request->setShipperContactCompanyName($store->getName());
        $request->setShipperContactPhoneNumber($store->getPhone());
        $request->setShipperEmail($storeAdmin->getEmail());
        $request->setShipperAddressStreet(trim($originStreet . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );
        $request->setShipperAddressStateOrProvinceCode($regionCode);
        $request->setShipperAddressPostalCode(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );
        $request->setShipperAddressCountryCode(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $shipmentStoreId
            )
        );
    }
   
   }
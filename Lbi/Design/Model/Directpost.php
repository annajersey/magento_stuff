<?php

namespace Lbi\Design\Model;

class Directpost extends \Magento\Authorizenet\Model\Directpost
{
	 protected function fillPaymentByResponse(\Magento\Framework\DataObject $payment)
    {
		parent::fillPaymentByResponse($payment);
		$response = $this->getResponse();
		$payment->setCcType($response->getXCardType());
	}
}
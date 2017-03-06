<?php

namespace Lbi\Design\Block;

class Payment extends \Magento\Sales\Block\Order\Info
{
	public function toHtml() {
		return $this->getOrder()->getPayment()->getMethod()=='authorizenet_directpost' ? parent::toHtml() : '';
	}
	public function getCcLast4()
    {
		return $this->getOrder()->getPayment()->decrypt($this->getOrder()->getPayment()->getCcLast4());
	}
	public function getAmountPaid()
    {
		return $this->getOrder()->getPayment()->getAmountPaid();
	}
	public function getCcType()
    {	
		return $this->getOrder()->getPayment()->getCcType();
	}
	
	
}	
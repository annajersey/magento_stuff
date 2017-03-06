<?php
namespace Lbi\Tax\Model;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Model\Calculation\AbstractCalculator;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxDetails\AppliedTax;
use Magento\Tax\Model\TaxDetails\AppliedTaxRate;
use Magento\Tax\Model\TaxDetails\TaxDetails;
use Magento\Store\Model\StoreManagerInterface;


class TaxCalculation extends \Magento\Tax\Model\TaxCalculation
{  

	 
	 protected  $_resource;
	  public function __construct(
		\Magento\Framework\App\ResourceConnection $resource,
        Calculation $calculation,
        CalculatorFactory $calculatorFactory,
        Config $config,
        TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        StoreManagerInterface $storeManager,
        TaxClassManagementInterface $taxClassManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->_resource = $resource;
       
		parent::__construct($calculation,$calculatorFactory,$config,$taxDetailsDataObjectFactory,$taxDetailsItemDataObjectFactory,$storeManager,$taxClassManagement,$dataObjectHelper);
    }
	 
	 protected function processItem(
        QuoteDetailsItemInterface $item,
        AbstractCalculator $calculator,
        $round = true
    ) {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test7.log');
			$logger = new \Zend\Log\Logger();
			$logger->addWriter($writer);
			
        $quantity = $this->getTotalQuantity($item);
		$test = $calculator->calculate($item, $quantity, $round);
		$taxes = $test->getAppliedTaxes();
		// if($item->getUnitPrice()>5){
			// $rowTax = $test->getRowTax();
			// foreach($taxes as $key=>$value){
				// $logger->info($key);
				// $select = $this->_resource->getConnection()->select();
				// $select->from(
					// ['main_table' => 'tax_calculation_rate'],
					// ['rate']
				// )->where('tax_calculation_rate_id = 3');
				// $Rate110 = $this->_resource->getConnection()->fetchOne($select);
				// $logger->info($Rate110);
				// $logger->info($rowTax);
				// $price = $this->calculation->round($item->getUnitPrice());
				// $rowTotal = $price * $quantity;
				// $rowTaxPerRate = $this->calculation->calcTaxAmount($rowTotal, $Rate110, false, false);
				// $logger->info($rowTaxPerRate);
				
				// // $logger->info($value->getCode());
				// // $logger->info($value->getTitle());
				
				
			// }
		// }
		//if($item->getUnitPrice()>5)  $test->setRowTax(0);
        return $test;
    }
    
}


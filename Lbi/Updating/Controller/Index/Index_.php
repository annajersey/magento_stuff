<?php
 
namespace Lbi\Updating\Controller\Index;
 
use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    
    
	/**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
	
	
    /**
    * @param Magento\Framework\App\Action\Context $context
    * @param \Magento\Catalog\Model\ProductRepository $productRepository
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository	
    ) {
        $this->_productRepository = $productRepository;
		 parent::__construct($context);
    }
 
  
    public function execute()
    {
        $filepath ='update_products/data.csv';
		$file = fopen($filepath, 'r');
		$i=0;
		file_put_contents('update_products/update_products.log',date('Y-m-d H:i',time()). PHP_EOL,FILE_APPEND);
		while (($line = fgetcsv($file)) !== FALSE) {
		  $i++;
		  if($i==1) continue;
		  $sku = $line[3];  echo 'sku:'.$sku.'<br>';
		  try{
		  $product = $this->_productRepository->get($sku);
		 // if(!$product) echo 'product does not exists<br>'; continue;
		  echo 'product id: '.$product->getEntityId().'<br>';
		  $qty = $line[0]+$line[1]; echo 'qty:'.$qty.'<br>'; 
		  $price = $line[2];  echo 'price:'.$price.'<br><br>'; 
		  file_put_contents('update_products/update_products.log',$product->getEntityId().' '.$sku.' '.$qty.' '.$price.PHP_EOL,FILE_APPEND);
		   
			   $product->setData('price',$price);
			   $product->setQuantityAndStockStatus(['qty' => $qty]);
			   $product->save();
		   }catch(\Exception $e) {
			   echo $e->getMessage().'<br>';
			   file_put_contents('update_products/errors.log',date('Y-m-d',time()).' '.$e->getMessage(). PHP_EOL,FILE_APPEND);
		   }
		}
		fclose($file);
		copy($filepath, 'archived/'.date('m_d_Y_H_i',time()).'.csv');
		die('ok');
    }
}
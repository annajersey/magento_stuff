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
        foreach (glob("update_products/Inventory_snapshot_*.txt") as $file) {
		  $files[] = $file;
		}
		
		$data = array();
		if(empty($files)) die('there no files for import');
		foreach($files as $filepath){
			$file = fopen($filepath, 'r');
			$i=0;
			file_put_contents('update_products/update_products.log',date('Y-m-d H:i',time()). PHP_EOL,FILE_APPEND);
				while (($line = fgetcsv($file, 0, "\t")) !== FALSE) {
				  $i++;
				  if($i==1) continue;
				  $sku = $line[0];  
				  $qty = $line[1]; 
				  if(!isset($data[$sku])) $data[$sku]=$qty ;
				  else $data[$sku]+=$qty;
				}
				fclose($file);
				rename($filepath, 'archived/'.basename($filepath, ".txt").'_'.date('m_d_Y_H_i',time()).'.txt');
		}	
		
		foreach($data as $sku=>$qty){
			try{
			$product = $this->_productRepository->get($sku);
			echo 'product id: '.$product->getEntityId().' - '.$qty.'<br>';
			if(!$product) continue;
			file_put_contents('update_products/update_products.log',$product->getEntityId().' '.$sku.' '.$qty.PHP_EOL,FILE_APPEND);
				$product->setQuantityAndStockStatus(['qty' => $qty]);
				$product->save();
			}catch(\Exception $e) {
				echo $e->getMessage();
				file_put_contents('update_products/errors.log',date('Y-m-d',time()).' '.$e->getMessage(). PHP_EOL,FILE_APPEND);
			}
		}
		//echo '<pre>'; print_r($data); echo '</pre>';		
		die('ok');
    }
}
<?php


namespace Lbi\Design\Block\Product\View\Type;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Pricing\PriceCurrencyInterface;
class Configurable  extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
{

    protected $jsonEncoder;
    protected $jsonDecoder;  protected $_logger; protected $helper; protected $configurableAttributeData;

    public function __construct(
		 DecoderInterface $jsonDecoder,
		 \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        array $data = []
		
	
    ) {
       $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
		 $this->_logger = $logger;
        parent::__construct(
        $context,
        $arrayUtils,
         $jsonEncoder,
         $helper,
         $catalogProduct,
      $currentCustomer,
         $priceCurrency,
       $configurableAttributeData,
         $data 
        );
    }



    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $config
    )
    {
		$currentProduct = $this->getProduct();
		$options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);
		
        $config = $this->jsonDecoder->decode($config);
        $this->_logger->debug(print_r($attributesData, true));
		
		$config['chooseText'] = $config['attributes']['90']['label'];
        return $this->jsonEncoder->encode($config);
    }
}
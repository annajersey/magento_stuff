<?php
namespace Lbi\Spec\Block;
 
class Brands extends \Magento\Framework\View\Element\Template
{
    
	protected $eavAttributeRepository;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,       
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
    ) {
        parent::__construct($context);
        $this->eavAttributeRepository = $eavAttributeRepository;
    }
	public function getBrands()
    {
       
		$attributes = $this->eavAttributeRepository->get(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,'brand');
        $brands = $attributes->getSource()->getAllOptions(false);
		return $brands;
    }
}
<?php
 
namespace Lbi\Ylprod\Model;

class ProductNeedRelationsFactory
{
    protected $_objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function create($className, array $data = [])
    {
        return $this->_objectManager->create($className, $data);
    }
}
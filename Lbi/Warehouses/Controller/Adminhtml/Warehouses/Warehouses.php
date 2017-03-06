<?php
namespace Lbi\Warehouses\Controller\Adminhtml\Warehouses;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Lbi\Warehouses\Model\WarehousesFactory;

abstract class Warehouses extends Action
{
    protected $_coreRegistry;
    protected $_resultPageFactory;
    protected $_warehousesFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        WarehousesFactory $warehousesFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_warehousesFactory = $warehousesFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lbi_Warehouses::warehouses');
    }
}
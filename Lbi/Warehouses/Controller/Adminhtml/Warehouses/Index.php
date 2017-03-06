<?php
namespace Lbi\Warehouses\Controller\Adminhtml\Warehouses;

class Index extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $_coreRegistry;
	
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
    }
	
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Lbi_Warehouses::warehouses');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Warehouses'));
        return $resultPage;
    }
	
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lbi_Warehouses::warehouses');
    }
}
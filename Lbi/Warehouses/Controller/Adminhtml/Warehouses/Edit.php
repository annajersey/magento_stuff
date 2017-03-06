<?php
namespace Lbi\Warehouses\Controller\Adminhtml\Warehouses;

class Edit extends Warehouses
{
    public function execute()
    {
        $warehouseId = $this->getRequest()->getParam('id');
        $warehousesModel = $this->_warehousesFactory->create('\Lbi\Warehouses\Model\Warehouses');
		$pageTitle = __('Add Warehouse');

        if (!empty($warehouseId) && (int) $warehouseId > 0) {
            $warehousesModel->load($warehouseId);
            if (!$warehousesModel->getId()) {
                $this->messageManager->addError(__('This warehouse no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
			$warehouseTitle = $warehousesModel->getName();
            $pageTitle = __('Edit Warehouse') . ' - ' . $warehouseTitle;
        }
		
        $this->_coreRegistry->register('warehouses_warehouses', $warehousesModel);
		
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Lbi_Warehouses::warehouses');
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);
		
        return $resultPage;
    }
}
<?php
namespace Lbi\Warehouses\Controller\Adminhtml\Warehouses;

class Save extends Warehouses
{
    public function execute()
    {
        $isPost = $this->getRequest()->getPost();
		
        if (!empty($isPost)) {
            $warehousesModel = $this->_warehousesFactory->create('\Lbi\Warehouses\Model\Warehouses');
            $warehouseId = $this->getRequest()->getParam('id');
            $formData = $this->getRequest()->getParam('warehouses');
            $warehouseName = $formData['name']; 
			
            if (!empty($warehouseId) && (int) $warehouseId > 0) {
                $warehousesModel->load($warehouseId);
            }
			
            $warehousesModel->setData($formData);
			
            try {
                $warehousesModel->save();
                $warehouseId = $warehousesModel->getId();

                $this->messageManager->addSuccess(__('The warehouse has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $warehouseId, '_current' => true]);
                    return;
                }
				
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
			
            $this->_redirect('*/*/edit', ['id' => $warehouseId]);
        }
    }
}
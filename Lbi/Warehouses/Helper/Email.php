<?php
namespace Lbi\Warehouses\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Lbi\Warehouses\Model\Mail\Template\MailTransport;
use Lbi\Warehouses\Model\WarehousesFactory;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_PICKSHEET_EMAIL_TEMPLATE  = 'picksheet/email/template';
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_temp_id;
    protected $_warehousesFactory;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        MailTransport $transportBuilder,
        WarehousesFactory $warehousesFactory
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_warehousesFactory = $warehousesFactory;
    }

    protected function _getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getTemplateId($xmlPath)
    {
        return $this->_getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->_temp_id)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;        
    }

    public function pickSheetWarehouseMailSend($warehouseId, $emailTemplateVariables, $file)
    {
        $receiverInfo = ['name' => 'Pickers', 'email' => ''];
        $warehousesModel = $this->_warehousesFactory->create('\Lbi\Warehouses\Model\Warehouses');
        $warehouse = $warehousesModel->load($warehouseId);
        if (!empty($warehouse) && !empty($warehouse->getEmail())) {
            $receiverInfo['email'] = $warehouse->getEmail();
        }

        $senderInfo = ['name' => 'Admin', 'email' => ''];
        $senderName = $this->_getConfigValue('trans_email/ident_general/name', $this->getStore()->getStoreId());
        if (!empty($senderName)) {
            $senderInfo['name'] = $senderName; 
        }
        $senderEmail = $this->_getConfigValue('trans_email/ident_general/email', $this->getStore()->getStoreId());
        if (!empty($senderEmail)) {
            $senderInfo['email'] = $senderEmail; 
        }

        if (!empty($receiverInfo['email']) && !empty($senderInfo['email'])) {
            $this->_temp_id = $this->getTemplateId(self::XML_PATH_PICKSHEET_EMAIL_TEMPLATE);
            $this->_inlineTranslation->suspend();    
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);    
            $transport = $this->_transportBuilder->attachFile($file)->getTransport();
            $transport->sendMessage();        
            $this->_inlineTranslation->resume();
        }
    }
}
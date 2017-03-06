<?php
namespace Lbi\Warehouses\Block\Adminhtml\Warehouses\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;

class Info extends Generic implements TabInterface
{
    protected $_wysiwygConfig;
    protected $_newsStatus;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }
	
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('warehouses_warehouses');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('warehouses_');
        $form->setFieldNameSuffix('warehouses');
		
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General')]
        );
		
        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
		
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'required' => true
            ]
        );
		
        $fieldset->addField(
            'location',
            'text',
            [
                'name' => 'location',
                'label' => __('Location'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                 'required' => true
            ]
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);
		
        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Warehouse Info');
    }
	
    public function getTabTitle()
    {
        return __('Warehouse Info');
    }
	
    public function canShowTab()
    {
        return true;
    }
	
    public function isHidden()
    {
        return false;
    }
}
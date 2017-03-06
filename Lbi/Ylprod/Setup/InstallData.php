<?php

namespace Lbi\Ylprod\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {

    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY, 'minimum_product_item_qty', [
            'group'=> 'General',
            'type'=>'varchar',
            'backend'=>'',
            'frontend'=>'',
            'label'=>'Minimum Quantity of Each Product Item',
            'input'=>'text',
            'class'=>'',
            'source'=>'',
            'global'=>'',
            'visible'=>true,
            'required'=>false,
            'user_defined'=>true,
            'default'=>'',
            'searchable'=>false,
            'filterable'=>false,
            'comparable'=>false,
            'visible_on_front'=>false,
			'is_visible_in_grid'=>true,
            'used_in_product_listing'=>false,
            'unique'=>false,
            'apply_to'=>'grouped,bundle,configurable'
           ]);
		   
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY, 'product_code', [
            'group'=> 'General',
            'type'=>'varchar',
            'backend'=>'',
            'frontend'=>'',
            'label'=>'Product Code',
            'input'=>'text',
            'class'=>'',
            'source'=>'',
            'global'=>'',
            'visible'=>true,
            'required'=>false,
            'user_defined'=>true,
            'default'=>'',
            'searchable'=>false,
            'filterable'=>false,
            'comparable'=>false,
            'visible_on_front'=>false,
			'is_visible_in_grid'=>true,
            'used_in_product_listing'=>false,
            'unique'=>false,
            'apply_to'=>'simple,virtual,downloadable,grouped,bundle,configurable'
           ]);
    }
}
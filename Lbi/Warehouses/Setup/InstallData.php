<?php
namespace Lbi\Warehouses\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [];
        $statuses = [
            'shipped_complete_store_1' => __('Shipped Complete - Store 1'),
            'shipped_complete_store_2' => __('Shipped Complete - Store 2'),
            'partially_shipped_store_1' => __('Partially Shipped - Store 1'),
            'multi_store_shipment_partial' => __('Multi Store Shipment - Partial'),
            'multi_store_shipment_complete' => __('Multi Store Shipment - Complete'),
            'not_shipped_store_1' => __('Not Shipped - Store 1'),
        ];
		
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        
		$setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);
		
        $data = [];
        $data[] = ['status' => 'not_shipped_store_1', 'state' => 'new', 'is_default' => 0, 'visible_on_front' => 1];
		$setup->getConnection()->insertArray(
            $setup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default', 'visible_on_front'],
            $data
        );

        $setup->getConnection()->update(
            $setup->getTable('sales_order_status_state'),
            ['state' => 'new', 'is_default' => 1, 'visible_on_front' => 1],
            ['status = ?' => 'pending']
        );
    }
}
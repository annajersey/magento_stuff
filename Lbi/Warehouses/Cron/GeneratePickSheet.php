<?php
namespace Lbi\Warehouses\Cron;

use Lbi\Warehouses\Model\Sales\Order\Pdf\Picksheet;

class GeneratePickSheet {
    protected $_picksheet;

    public function __construct(
        Picksheet $picksheet
    ) {
        $this->_picksheet = $picksheet;
    }

    public function pickSheetWarehouseFirst()
    {
        $this->_picksheet->generatePickSheetWarehouse(1);
		return true;
    }

    public function pickSheetWarehouseSecond()
    {
        $this->_picksheet->generatePickSheetWarehouse(2);
        return true;
    }
}
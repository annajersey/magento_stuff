<?php
namespace Lbi\Warehouses\Model\Sales\Order\Pdf;

use Magento\Payment\Helper\Data as PaymentData;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Sales\Model\Order\Pdf\Config as PdfConfig;
use Magento\Sales\Model\Order\Pdf\Total\Factory as PdfTotalFactory;
use Magento\Sales\Model\Order\Pdf\ItemsFactory as PdfItemsFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Catalog\Model\Factory as ProductFactoryModel;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Lbi\Warehouses\Helper\Email;
use Magento\Sales\Model\Order\Item as OrderItem; 

class Picksheet extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    protected $_pickListDate;
    protected $_storeManager;
    protected $_localeResolver;
    protected $_string;
    protected $_filterManager;
    protected $_directoryList;
    protected $_productFactoryModel;
    protected $_productOption;
    protected $_mediaConfig;
    protected $_orderCollectionFactory;
    protected $_email;
	protected $_orderItem;

    public function __construct(
        PaymentData $paymentData,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        PdfConfig $pdfConfig,
        PdfTotalFactory $pdfTotalFactory,
        PdfItemsFactory $pdfItemsFactory,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        AddressRenderer $addressRenderer,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
	    FilterManager $filterManager,
	    ProductFactoryModel $productFactoryModel,
		MediaConfig $mediaConfig,
        OrderCollectionFactory $orderCollectionFactory,
		Email $email,
		OrderItem $orderItem,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
	    $this->_string = $string;
	    $this->_filterManager = $filterManager;
	    $this->_productFactoryModel = $productFactoryModel;
		$this->_mediaConfig = $mediaConfig;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_email = $email;
		$this->_orderItem = $orderItem;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $data
        );
    }

    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);

        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));
        $this->y -= 10;

        $lines[0][] = ['text' => __('Image'), 'feed' => 35];
        $lines[0][] = ['text' => __('Product'), 'feed' => 125];
        $lines[0][] = ['text' => __('Qty'), 'feed' => 400];
        $lines[0][] = ['text' => __('SKU'), 'feed' => 450];
        $lineBlock = ['lines' => $lines, 'height' => 10];
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }
	
    protected function _drawOrderItem(\Magento\Sales\Model\Order\Item $item, \Zend_Pdf_Page $page, \Magento\Sales\Model\Order $order)
    {	
        $_product = $item->getProduct();
        $itemName = $item->getName();
        $itemQty = $this->_getQtyToShip($item);
        $options = [];
        $configurableOptions = [];
        $heightCoefficient = 0;

        if (!empty($item->getParentItem()) && !empty($item->getParentItemId())) {
            $parentItem = $this->_orderItem->load($item->getParentItemId());
            if (!empty($parentItem) && $parentItem->getProductType() == 'configurable') {
                $itemName = $parentItem->getName();
                $itemConfigurableOptions = $parentItem->getProductOptions();
                if (!empty($itemConfigurableOptions['attributes_info'])) {
                    $configurableOptions = $itemConfigurableOptions['attributes_info'];
                } elseif (is_string($itemConfigurableOptions)) {
                    $itemConfigurableOptions = unserialize($itemConfigurableOptions);
                    if (!empty($itemConfigurableOptions['attributes_info'])) {
                        $configurableOptions = $itemConfigurableOptions['attributes_info'];
                    }
                }
            }
        }

        $customOptions = $this->_orderItem->load($item->getId())->getProductOptions();
        if (!empty($customOptions['options'])) {
            $options = $customOptions['options'];
        } elseif (is_string($customOptions)) { 
            $customOptions = unserialize($customOptions);
            if (!empty($customOptions['options'])) { 
                $options = $customOptions['options'];
            }
        }

        $lines = [];
        $lines[0] = [['text' => $this->string->split($itemName, 60, true, true), 'feed' => 125]];
        $lines[0][] = ['text' => $itemQty, 'feed' => 400];
        $lines[0][] = [
            'text' => $this->string->split($item->getSku(), 25),
            'feed' => 450,
        ];
        $lineBlock = ['lines' => $lines, 'height' => 10];
        $page = $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        if (!empty($options)) {
            $lines2 = [];
            foreach ($options as $option) {
                $lines2[][] = [
                    'text' => $this->string->split($this->_filterManager->stripTags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'font_size' => 7,
                    'feed' => 130,
                ];
                if ($option['value']) {
                    $printValue = isset($option['print_value']) ? $option['print_value'] : $this->_filterManager->stripTags($option['value']);
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines2[][] = ['text' => $this->string->split($value, 50, true, true), 'feed' => 135, 'font_size' => 7];
                    }
                }
            }
            if (!empty($lines2)) {
                $lineBlock2 = ['lines' => $lines2, 'height' => 8, 'font_size' => 7];
                $page = $this->drawLineBlocks($page, [$lineBlock2], ['table_header' => true]);
                $heightCoefficient = count($lineBlock2) * 5;
                $this->y += $heightCoefficient;
            }
        }

        if (!empty($configurableOptions)) {
            $lines2 = [];
            foreach ($configurableOptions as $option) {
                $lines2[][] = [
                    'text' => $this->string->split($this->_filterManager->stripTags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'font_size' => 7,
                    'feed' => 130,
                ];
                if ($option['value']) {
                    $printValue = isset($option['print_value']) ? $option['print_value'] : $this->_filterManager->stripTags($option['value']);
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines2[][] = ['text' => $this->string->split($value, 50, true, true), 'feed' => 135, 'font_size' => 7];
                    }
                }
            }
            if (!empty($lines2)) {
                $lineBlock2 = ['lines' => $lines2, 'height' => 8, 'font_size' => 7];
                $page = $this->drawLineBlocks($page, [$lineBlock2], ['table_header' => true]);
                $heightCoefficient = count($lineBlock2) * 5;
                $this->y += $heightCoefficient;
            }
        }

        if (!empty($_product)) {
            $productDescription = strip_tags(trim($_product->getDescription())); 
        } else {
            $productDescription = '';
        }
        
        if (!empty($productDescription)) {
            $productDescription = substr($productDescription, 0, 400) . "...";
            $productDescriptionLines = $this->string->split($this->_filterManager->stripTags($productDescription), 150, true, true);
            $lines3 = [];
            $lines3[0][] = ['text' => 'Description:', 'feed' => 125, 'font_size' => 7,];
            $lines3[1][] = [
                'text' => $productDescriptionLines,
                'font' => 'italic',
                'feed' => 125,
                'font_size' => 7,
            ];
            $lineBlock3 = ['lines' => $lines3, 'height' => 8];
            $this->y -= $heightCoefficient;
            $page = $this->drawLineBlocks($page, [$lineBlock3], ['table_header' => true]);
            $heightCoefficient2 = (count($productDescriptionLines) * 5) + 5;
            $this->y += $heightCoefficient2 + $heightCoefficient;
        } else {
            $this->y -= 9;
        }

        $this->y += 10;
        $this->_insertProductImg($page, $item->getProductId());
        $this->y -= 75;
        $this->setPage($page);
        $this->y -= 20;
        return $page;
    }

    protected function _insertProductImg(&$page, $productId)
    {	
        $_product = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($productId);
        if (!empty($_product)) {
            $imageUrl = $this->_mediaConfig->getMediaUrl($_product->getSmallImage());
            if (!empty($imageUrl)) {
                $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
                $imageUrlExtArr = explode(".", $imageUrl);
                $imageUrlExt = $imageUrlExtArr[count($imageUrlExtArr)-1];
                if ($imageUrlExt == 'gif') {
                    $imageUrl = $baseUrl . 'pub/static/adminhtml/Magento/backend/en_US/Magento_Catalog/images/product/placeholder/thumbnail.jpg';
                }
                $imagePath = str_replace(array($baseUrl, '/'), array('', DIRECTORY_SEPARATOR), $imageUrl);
                $this->y = $this->y ? $this->y : 800;
                if (!empty($imagePath) && is_file($imagePath)) {
                    $image = \Zend_Pdf_Image::imageWithPath($imagePath);
                    $top = $this->y + 20;
                    $widthLimit = 75;
                    $heightLimit = 75;
                    $width = $image->getPixelWidth();
                    $height = $image->getPixelHeight(); 
                    $ratio = $width / $height;

                    if ($ratio > 1 && $width > $widthLimit) {
                        $width = $widthLimit;
                        $height = $width / $ratio;
                    } elseif ($ratio < 1 && $height > $heightLimit) {
                        $height = $heightLimit;
                        $width = $height * $ratio;
                    } elseif ($ratio == 1 && $height > $heightLimit) {
                        $height = $heightLimit;
                        $width = $widthLimit;
                    }

                    $y1 = $top - $height;
                    $y2 = $top;
                    $x1 = 35;
                    $x2 = $x1 + $width;
                    $page->drawImage($image, $x1, $y1, $x2, $y2);
                }
            }
        }
    }
	
    protected function _checkOrder($order)
    {
        if ($order->canUnhold() || $order->isPaymentReview()) {
            return false;
        }

        if ($order->isCanceled()) {
            return false;
        }

        if ($order->getActionFlag($order::ACTION_FLAG_SHIP) === false) {
            return false;
        }
        
        foreach ($order->getAllItems() as $item) {
            if ($this->_getQtyToShip($item) > 0 && !$item->getLockedDoShip()) {
                return true;
            }
        }

        return false;
    }

    protected function _checkItem($item)
    {
        if ($item->getProductType() == 'configurable' || $item->getProductType() == 'downloadable' || $item->getProductType() == 'bundle' || $item->getProductType() == 'virtual') {
            return false;		
        }

        if ($this->_getQtyToShip($item) <= 0 || $item->getLockedDoShip()) {
            return false;
        }

        return true;
    }

    protected function _getQtyToShip($item)
    {
        $qty = 0;
        $productId = (int) $item->getProductId();
        $_product = $this->_productFactoryModel->create('\Magento\Catalog\Model\Product')->load($productId);
        if (!empty($_product)) {
            if (!empty($item->getParentItem())) {
                $item = $item->getParentItem();
            }
            $qty = $item->getQtyOrdered() - $item->getQtyShipped() - $item->getQtyRefunded() - $item->getQtyCanceled();
        }
        return max($qty, 0); 
    }

	protected function _sendPickSheetWarehouse($warehouseId, $date, $file)
    {
        $emailTemplateVariables = [];
        $emailTempVariables['date'] = $date;
        $emailTempVariables['file'] = basename($file);

        $this->_email->pickSheetWarehouseMailSend($warehouseId, $emailTempVariables, $file);	
    }

    public function generatePickSheetWarehouse($warehouseId)
    {
        $pickListDate = date('Y-m-d H:i:s');
        $pickListDateFile = date('Y-m-d_H-i-s');
        $folder = DirectoryList::VAR_DIR . DIRECTORY_SEPARATOR . 'pick_sheets';
        $file = sprintf('picksheet_%s.pdf', 'W' . $warehouseId . '_' . $pickListDateFile);
        if (!is_dir($folder)) {
            if (!mkdir($folder)) {
                exit('WRITE TO LOG - Cant create folder');
            }
        }

        $orderFilters = [];
        if ($warehouseId == 1) {
            $orderFilters['status'] = ['in' => ['pending', 'processing']];
        } else {
            $orderFilters['status'] = ['in' => ['partially_shipped_store_1', 'not_shipped_store_1']];
        }

        #$orderFilters['entity_id'] = ['gt' => 142]; # REMOVE AFTER TEST !!!

        $orders = $this->_orderCollectionFactory->create(); 

        if (!empty($orderFilters)) {
            foreach ($orderFilters as $filterField => $filterCondition) {
                $orders->addFieldToFilter($filterField, $filterCondition);
            }
        }

        if ($orders->count()) {
            file_put_contents($folder . DIRECTORY_SEPARATOR . $file, $this->getPdfPickList($orders, $pickListDate)->render());
            $this->_sendPickSheetWarehouse($warehouseId, $pickListDate, $folder . DIRECTORY_SEPARATOR . $file);
        }
        return true;
    }

    public function getPdfPickList($orders = [], $pickListDate)
    {
        $this->_pickListDate = $pickListDate;
        return $this->getPdf($orders);
    }

    public function getPdf($orders = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($orders as $order) {

            if (!$this->_checkOrder($order)) {
				continue;
			}

            if ($order->getStoreId()) {
                $this->_localeResolver->emulate($order->getStoreId());
                $this->_storeManager->setCurrentStore($order->getStoreId());
            }

            $page = $this->newPage();
            $this->insertLogo($page, $order->getStore());
            $this->insertAddress($page, $order->getStore());
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            $this->insertDocumentNumber($page, __('Pick Sheet: ') . $this->_pickListDate);

            $this->_drawHeader($page);

            foreach ($order->getAllItems() as $item) {
                if (!$this->_checkItem($item)) {
                    continue;
                }
                $this->_drawOrderItem($item, $page, $order);
                $page = end($pdf->pages);
            }
        }

        $this->_afterGetPdf();
        return $pdf;
    }

    public function newPage(array $settings = [])
    {
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
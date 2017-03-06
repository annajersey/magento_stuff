<?php

namespace Lbi\Warehouses\Model\Shipping;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping\LabelsFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Framework\Filesystem;

class LabelGenerator extends \Magento\Shipping\Model\Shipping\LabelGenerator
{
	
	protected $_storeManager;
	
	protected $carrierFactory;
    protected $labelFactory;
    protected $scopeConfig;
    protected $trackFactory;
    protected $filesystem;
	
	public function __construct(
        StoreManagerInterface $storeManager,
		CarrierFactory $carrierFactory,
        LabelsFactory $labelFactory,
        ScopeConfigInterface $scopeConfig,
        TrackFactory $trackFactory,
        Filesystem $filesystem
    ) {
        $this->_storeManager = $storeManager;
		$this->carrierFactory = $carrierFactory;
        $this->labelFactory = $labelFactory;
        $this->scopeConfig = $scopeConfig;
        $this->trackFactory = $trackFactory;
        $this->filesystem = $filesystem;
        parent::__construct(
			$this->carrierFactory,
			$this->labelFactory,
			$this->scopeConfig,
			$this->trackFactory,
			$this->filesystem
		);
    }

	protected function _addLogoLabelsPdf($page) {
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
		$logoUrl = $baseUrl . 'pub/media/logo/default/logo-print.jpg';
		$logoPath = str_replace(array($baseUrl, '/'), array('', DIRECTORY_SEPARATOR), $logoUrl);
		$logoImage = \Zend_Pdf_Image::imageWithPath($logoPath);
		$page->drawImage($logoImage, 507, 645, 570, 730);
		return $page;
	}
	
	public function combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new \Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = \Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
					$page = $this->_addLogoLabelsPdf($page);
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->createPdfPageFromImageString($content);
                if ($page) {
					$page = $this->_addLogoLabelsPdf($page);
                    $outputPdf->pages[] = $page;
                }
            }
        }
        return $outputPdf;
    }
}

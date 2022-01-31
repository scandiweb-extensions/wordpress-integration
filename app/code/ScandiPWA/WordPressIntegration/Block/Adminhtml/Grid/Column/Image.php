<?php
/**
 * ScandiPWA_WordPressIntegration
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace ScandiPWA\WordPressIntegration\Block\Adminhtml\Grid\Column;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Image extends AbstractRenderer
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        return '<image width="100%" style="max-width: 500px;" src="'.$row->getData('image_url').'">';
    }
}

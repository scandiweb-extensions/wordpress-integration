<?php
/**
 * Scandiweb_WordPressIntegration
 *
 * @category    Scandiweb
 * @package     Scandiweb_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace Scandiweb\WordPressIntegration\Block\Adminhtml\Grid\Column;

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

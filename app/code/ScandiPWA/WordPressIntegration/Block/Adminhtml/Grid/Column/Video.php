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

class Video extends AbstractRenderer
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $videoUrl = $row->getData('video_url');
        $videoEmbed = $row->getData('video_embed');

        if (!empty($videoUrl)) {
            return '<video controls width="500"><source src="' . $videoUrl . '" /></video>';
        }

        if (!empty($videoEmbed)) {
            return '<div style="max-width: 500px; max-height: 400px;">' . $videoEmbed . '</div>';
        }

        return '';
    }
}

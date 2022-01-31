<?php
/**
 * ScandiPWA_WordPressIntegration
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace ScandiPWA\WordPressIntegration\Model;

class WordPressPost extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('ScandiPWA\WordPressIntegration\Model\ResourceModel\WordPressPost');
    }
}

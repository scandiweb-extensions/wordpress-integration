<?php
/**
 * Scandiweb_WordPressIntegration
 *
 * @category    Scandiweb
 * @package     Scandiweb_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace Scandiweb\WordPressIntegration\Model;

class WordPressPost extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('Scandiweb\WordPressIntegration\Model\ResourceModel\WordPressPost');
    }
}

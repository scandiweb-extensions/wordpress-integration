<?php
/**
 * ScandiPWA_WordPressIntegration
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace ScandiPWA\WordPressIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class WordPressPost extends AbstractDb
{
    public const ID_FIELD = 'entity_id';

    public const POST_ID_FIELD = 'post_id';

    public const STORE_ID_FIELD = 'store_id';

    protected function _construct()
    {
        $this->_init('wordpress_post', self::ID_FIELD);
    }
}

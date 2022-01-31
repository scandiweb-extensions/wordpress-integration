<?php
/**
 * ScandiPWA_WordPressIntegration
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace ScandiPWA\WordPressIntegration\Model\ResourceModel\WordPressPost;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use ScandiPWA\WordPressIntegration\Model\WordPressPost;
use ScandiPWA\WordPressIntegration\Model\ResourceModel\WordPressPost as WordPressPostResource;

class Collection extends AbstractCollection
{
    /**
     * This should match \ScandiPWA\WordPressIntegration\Model\ResourceModel\WordPressPost::ID_FIELD
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            WordPressPost::class,
            WordPressPostResource::class
        );
    }
}

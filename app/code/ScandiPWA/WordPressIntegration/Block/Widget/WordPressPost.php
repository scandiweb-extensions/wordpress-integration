<?php
/**
 * ScandiPWA_WordPressIntegration
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace ScandiPWA\WordPressIntegration\Block\Widget;

use ScandiPWA\WordPressIntegration\Model\WordPressPost as PostModel;
use ScandiPWA\WordPressIntegration\Model\WordPressPostFactory as PostModelFactory;
use ScandiPWA\WordPressIntegration\Model\ResourceModel\WordPressPost as PostResource;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class WordPressPost extends Template implements BlockInterface
{
    protected $_template = 'widget/wordpress_post.phtml';
    /**
     * @var PostModelFactory
     */

    protected $postModelFactory;

    /**
     * @var PostResource
     */
    protected $postResource;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param PostModelFactory $postModelFactory
     * @param PostResource $postResource
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        PostModelFactory $postModelFactory,
        PostResource $postResource,
        Template\Context $context,
        DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->postModelFactory = $postModelFactory;
        $this->postResource = $postResource;
        $this->dateTime = $dateTime;
    }

    /**
     * @return PostModel
     */
    public function getPost()
    {
        $post = $this->postModelFactory->create();
        $this->postResource->load($post, $this->getData('selected_post_id'));

        return $post;
    }

    /**
     * @param string $date
     * @return string
     */
    public function formatPostDate($date)
    {
        return $this->dateTime->gmtDate('j. F Y', strtotime($date));
    }
}

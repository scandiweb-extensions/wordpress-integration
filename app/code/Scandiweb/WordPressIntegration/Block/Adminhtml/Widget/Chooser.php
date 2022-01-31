<?php
/**
 * Scandiweb_WordPressIntegration
 *
 * @category    Scandiweb
 * @package     Scandiweb_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace Scandiweb\WordPressIntegration\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\Adminhtml\Widget\Chooser as CoreChooser;
use Scandiweb\WordPressIntegration\Model\ResourceModel\WordPressPost\CollectionFactory;
use Scandiweb\WordPressIntegration\Model\WordPressPostFactory;
use Scandiweb\WordPressIntegration\Model\ResourceModel\WordPressPost as PostResource;
use Scandiweb\WordPressIntegration\Block\Adminhtml\Grid\Column\Image;
use Scandiweb\WordPressIntegration\Block\Adminhtml\Grid\Column\Video;

/**
 * CMS block chooser for Wysiwyg CMS widget
 */
class Chooser extends Extended
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var WordPressPostFactory
     */
    protected $postFactory;

    /**
     * @var PostResource
     */
    protected $postResource;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param StoreManagerInterface $storeManager
     * @param WordPressPostFactory $postFactory
     * @param PostResource $postResource
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        StoreManagerInterface $storeManager,
        WordPressPostFactory $postFactory,
        PostResource $postResource,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->postFactory = $postFactory;
        $this->postResource = $postResource;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('wordpress/block_widget/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            CoreChooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $post = $this->postFactory->create();
            $this->postResource->load($post, $element->getValue());

            if ($post->getPostId()) {
                $chooser->setLabel($this->escapeHtml($post->getTitle()));
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        return '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var postId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                // Title is in third column = entity column + 2x .next()
                var postTitle = trElement.down("td").next().next().innerHTML; ' .
                $chooserJsObject . '.setElementValue(postId);' .
                $chooserJsObject . '.setElementLabel(postTitle);' .
                $chooserJsObject . '.close();
                
                window.filterChangeObserverInitialized = false;
            }';
    }

    /**
     * Grid row init js callback
     * This will reload grid on selecting different store in store filter
     *
     * @return string
     */
    public function getRowInitCallback()
    {
        return '
            function(grid, row){           
                window.filterChangeObserverInitialized = window.filterChangeObserverInitialized || false
    
                if (!window.filterChangeObserverInitialized) {         
                    var select = document.querySelector("select[name=\'store_id\']");
                    select.addEventListener(\'change\', function() {
                        grid.doFilter();
                        window.filterChangeObserverInitialized = false;
                    });
                    window.filterChangeObserverInitialized = true;   
                }
            }';
    }

    /**
     * Prepare Cms static blocks collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());
        return parent::_prepareCollection();
    }

    /**
     * Prepare store list for options
     *
     * @return array
     */
    protected function getStoresAsOptions()
    {
        $options = [];
        foreach($this->storeManager->getStores() as $store) {
            $options[$store->getId()] = $store->getName();
        }
        return $options;
    }

    /**
     * Prepare columns for Cms blocks grid
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            ['header' => __('Magento ID'), 'index' => 'entity_id']
        );

        $this->addColumn(
            'post_id',
            ['header' => __('WordPress ID'), 'index' => 'post_id']
        );

        $this->addColumn(
            'title',
            ['header' => __('Title'), 'index' => 'title']
        );

        $this->addColumn(
            'image',
            [
                'header' => __('Image'),
                'align' => 'center',
                'index' => 'image_url',
                'filter' => false,
                'sortable' => false,
                'renderer' => Image::class
            ]
        );

        $this->addColumn(
            'video',
            [
                'header' => __('Video'),
                'align' => 'center',
                'index' => 'video_url',
                'filter' => false,
                'sortable' => false,
                'renderer' => Video::class
            ]
        );

        $this->addColumn(
            'store_id',
            [
                'header' => __('Store'),
                'index' => 'store_id',
                'type' => 'options',
                'options' => $this->getStoresAsOptions(),
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('wordpress/block_widget/chooser', ['_current' => true]);
    }
}

<?php
/**
 * ScandiPWA_WordPressIntegration
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace ScandiPWA\WordPressIntegration\Controller\Adminhtml\Block\Widget;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;

class Chooser extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(Context $context, LayoutFactory $layoutFactory, RawFactory $resultRawFactory)
    {
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * Chooser Source action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Layout $layout */
        $layout = $this->layoutFactory->create();

        $uniqId = $this->getRequest()->getParam('uniq_id');
        $pagesGrid = $layout->createBlock(
            \ScandiPWA\WordPressIntegration\Block\Adminhtml\Widget\Chooser::class,
            '',
            ['data' => ['id' => $uniqId]]
        );

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($pagesGrid->toHtml());
        return $resultRaw;
    }
}

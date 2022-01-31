<?php
/**
 * ScandiPWA_WordPressIntegration
 *
 * @category    ScandiPWA
 * @package     ScandiPWA_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace ScandiPWA\WordPressIntegration\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use ScandiPWA\WordPressIntegration\Cron\GetBlogPosts;

class GetWordPressPosts extends Command
{
    /**
     * @var string URL
     */
    const URL = 'url';

    /**
     * @var string STORE_ID
     */
    const STORE_ID = 'store-id';

    /**
     * @var State $appState
     */
    protected $appState;

    /**
     * @var GetBlogPosts
     */
    protected $postHelper;

    /**
     * @param GetBlogPosts $postHelper
     * @param State $appState
     * @param null $name
     */
    public function __construct(
        GetBlogPosts $postHelper,
        State $appState,
        $name = null
    ) {
        parent::__construct($name);
        $this->appState = $appState;
        $this->postHelper = $postHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // Example value https://some-site.com/wp-json/wp/v2/posts?per_page=100
        // More info on WordPress api https://developer.wordpress.org/rest-api/reference/#rest-api-developer-endpoint-reference
        $this->setName('wordpress:get-posts')
             ->setDescription('Get posts from a WordPress site')
             ->addOption(
                self::URL,
                null,
                InputOption::VALUE_REQUIRED,
                'Url to a WordPress json api data source')
            ->addOption(
                self::STORE_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'ID of store to import posts for'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $exception) {
            $message = __('Area code already set')->getText();
            $output->writeln($message);
        }

        $url = $input->getOption(self::URL);
        $storeId = $input->getOption(self::STORE_ID);

        if (!$url) {
            $this->displayError('WordPress post json api url not specified', $output);
            $this->displayComment('Example:', $output);
            $this->displayComment('wordpress:get-posts --url=https://some-site.com/wp-json/wp/v2/posts?per_page=100 --store-id=1', $output);
            return;
        }

        if (!$storeId) {
            $this->displayError('Store ID to import posts to not specified', $output);
            $this->displayComment('Example:', $output);
            $this->displayComment('wordpress:get-posts --url=https://some-site.com/wp-json/wp/v2/posts?per_page=100 --store-id=1', $output);
            return;
        }

        $this->displayInfo('Processing url...', $output);
        $this->postHelper->getBlogPosts('', $storeId ,$url);
        $this->displayInfo('Url was processed!', $output);
    }

    /**
     * Display info in console
     *
     * @param string          $message
     * @param OutputInterface $output
     *
     * @return void
     */
    public function displayInfo(string $message, OutputInterface $output)
    {
        if (!empty($message)) {
            $coloredMessage = '<info>' . $message . '</info>';
            $output->writeln($coloredMessage);
        }
    }

    /**
     * Display comment in console
     *
     * @param string          $message
     * @param OutputInterface $output
     *
     * @return void
     */
    public function displayComment(string $message, OutputInterface $output)
    {
        if (!empty($message)) {
            $coloredMessage = '<comment>' . $message . '</comment>';
            $output->writeln($coloredMessage);
        }
    }

    /**
     * Display error in console
     *
     * @param string          $message
     * @param OutputInterface $output
     *
     * @return void
     */
    public function displayError(string $message, OutputInterface $output)
    {
        if (!empty($message)) {
            $coloredMessage = '<error>' . $message . '</error>';
            $output->writeln($coloredMessage);
        }
    }
}

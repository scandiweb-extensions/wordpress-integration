<?php
/**
 * Scandiweb_WordPressIntegration
 *
 * @category    Scandiweb
 * @package     Scandiweb_WordPressIntegration
 * @author      Raivis Dejus <info@scandiweb.com>
 */
declare(strict_types=1);

namespace Scandiweb\WordPressIntegration\Cron;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Scandiweb\WordPressIntegration\Model\ResourceModel\WordPressPost as WordPressPostResource;
use Scandiweb\WordPressIntegration\Model\ResourceModel\WordPressPost\Collection as WordPressPostCollection;
use Scandiweb\WordPressIntegration\Model\ResourceModel\WordPressPost\CollectionFactory as WordPressPostCollectionFactory;
use Scandiweb\WordPressIntegration\Model\WordPressPostFactory;


/**
 * This class will get blog posts for each store if it has WordPress integration settings saved
 * @package Scandiweb\WordPressIntegration\Cron
 */
class GetBlogPosts
{
    public const BLOG_SOURCE_URL_CONFIG = 'wordpress_integration/general/source_url';

    /**
     * For more info on WordPress API see
     * https://developer.wordpress.org/rest-api/reference/#rest-api-developer-endpoint-reference
     *
     * Value should not start with slash (/)
     */
    public const POST_API_PATH = 'wp-json/wp/v2/posts';

    /**
     * @var StoreManagerInterface
     */

    protected $storeManager;
    /**
     * @var ScopeConfigInterface
     */

    protected $scopeConfig;
    /**
     * @var Curl
     */

    protected $curl;
    /**
     * @var WordPressPostFactory
     */

    protected $wordPressPostFactory;
    /**
     * @var WordPressPostResource
     */

    protected $wordPressPostResource;
    /**
     * @var WordPressPostCollectionFactory
     */

    protected $wordPressPostCollectionFactory;

    /**
     * Cron constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     * @param WordPressPostFactory $wordPressPostFactory
     * @param WordPressPostResource $wordPressPostResource
     * @param WordPressPostCollectionFactory $wordPressPostCollectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        WordPressPostFactory $wordPressPostFactory,
        WordPressPostResource $wordPressPostResource,
        WordPressPostCollectionFactory $wordPressPostCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->wordPressPostFactory = $wordPressPostFactory;
        $this->wordPressPostResource = $wordPressPostResource;
        $this->wordPressPostCollectionFactory = $wordPressPostCollectionFactory;
    }

    /**
     * Get blog posts, save new posts in the DB
     */
    public function execute()
    {
        foreach ($this->getBlogConfigs() as $storeId => $blogHost) {
            $this->getBlogPosts($blogHost, $storeId);
        }
    }

    /**
     * Will get list of blogs that we need to get posts for
     * @return array
     */
    protected function getBlogConfigs(): array
    {
        $result = [];

        foreach ($this->storeManager->getStores() as $store) {
            $blogSourceUrl = $this->scopeConfig->getValue(
                self::BLOG_SOURCE_URL_CONFIG,
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );

            if (!empty($blogSourceUrl) && $store->getIsActive()) {
                $result[$store->getId()] = $blogSourceUrl;
            }
        }

        return $result;
    }

    /**
     * Will get posts API url
     * @param string $host
     * @return string
     */
    protected function getPostsApiUrl(string $host): string
    {
        if (substr($host, -1) !== '/') {
            $host .= '/';
        }

        return $host . self::POST_API_PATH;
    }

    /**
     * Will get curl data from the host
     * @param string $url
     * @return string
     */
    protected function getRemoteData(string $url): string
    {
        // Will add headers to get pass CloudFlare
        $this->curl->addHeader('user-agent', 'Magento');
        $this->curl->addHeader('content-type', 'application/json');
        $this->curl->get($url);

        if ($this->curl->getStatus() !== 200) {
            throw new \RuntimeException('Failed to get remote WordPress data from ' . $url);
        }

        return $this->curl->getBody();
    }

    /**
     * Will get posts media urls.
     * Should return array with two items, image url and video url.
     *
     * @param array $post
     * @return array
     */
    protected function getPostMedia(array $post): array
    {
        if (!isset($post['_links']['wp:attachment'][0]['href'])) {
            return ['', ''];
        }

        $attachments = json_decode($this->getRemoteData($post['_links']['wp:attachment'][0]['href']), true);

        $imageUrl = '';
        $videoUrl = '';
        foreach ($attachments as $attachment) {
            if (empty($imageUrl) && $attachment['media_type'] === 'image'
            ) {
                if (isset($attachment['media_details']['sizes']['large']['source_url'])) {
                    $imageUrl = $attachment['media_details']['sizes']['large']['source_url'];
                } else if (isset($attachment['media_details']['sizes']['full']['source_url'])) {
                    // If image is too small and does not have 'large' size we take 'full'
                    $imageUrl = $attachment['media_details']['sizes']['full']['source_url'];
                }
            }

            if (empty($videoUrl) && strpos($attachment['mime_type'], 'video/') !== false) {
                $videoUrl = $attachment['source_url'];
            }
        }

        return [$imageUrl, $videoUrl];
    }

    /**
     * Will get embedded video code from post, like YouTube.
     *
     * @param array $post
     * @return string
     */
    protected function getPostEmbed(array $post): string
    {
        if (strpos($post['content']['rendered'], '<iframe') !== false) {
            $iframes = [];
            preg_match('/<iframe.*<\/iframe>/',$post['content']['rendered'], $iframes);

            if (!empty($iframes)) {
                return preg_replace(
                    [
                        '/width="[0-9]*"/',
                        '/height="[0-9]*"/',
                        '/width=\\"[0-9]*\\"/',
                        '/height=\\"[0-9]*\\"/',
                        '/height=\'[0-9]*\'/',
                        '/style="[a-zA-Z0-9:; _-]*"/',
                        '/style=\\"[a-zA-Z0-9:; _-]*\\"/',
                        '/style=\'[a-zA-Z0-9:; _-]*\'/'
                    ],
                    '',
                    $iframes[0]
                );
            }
        }

        return '';
    }

    /**
     * Will get embedded video tags from post.
     *
     * @param array $post
     * @return string
     */
    protected function getPostEmbeddedVideo(array $post): string
    {
        if (strpos($post['content']['rendered'], '<video') !== false) {
            $videoTags = [];
            preg_match('/<video.*<\/video>/',$post['content']['rendered'], $videoTags);

            if (!empty($videoTags)) {
                $videoTags[0] = preg_replace(
                    [
                        '/class="[0-9a-z-_]*"/',
                        '/class=\\"[0-9a-z-_]*\\"/',
                        '/width="[0-9]*"/',
                        '/height="[0-9]*"/',
                        '/width=\\"[0-9]*\\"/',
                        '/height=\\"[0-9]*\\"/',
                    ],
                    '',
                    $videoTags[0]
                );

                // Will replace video id, so we can start playing it from JS
                $videoTags[0] = preg_replace(
                    [
                        '/id="[0-9a-z-_]*"/',
                        '/id=\\"[0-9a-z-_]*\\"/',
                        '/id=\'[0-9a-z-_]*\'/',
                    ],
                    'id="video-' . $post["id"] . '"',
                    $videoTags[0]
                );

                return $videoTags[0];
            }
        }

        return '';
    }

    /**
     * Will get related post category data.
     * First category will be selected.
     * Should return array with two items, category label and url.
     *
     * @param array $post
     * @return array
     */
    protected function getPostCategory(array $post): array
    {
        // term[0] is for categories
        // term[1] is for tags
        if (!isset($post['_links']['wp:term'][0]['href'])) {
            return ['', ''];
        }

        $categories = json_decode($this->getRemoteData($post['_links']['wp:term'][0]['href']), true);

        $imageUrl = '';
        $videoUrl = '';
        if (!empty($categories)) {
            return [$categories[0]['name'], $categories[0]['link']];
        }

        return [$imageUrl, $videoUrl];
    }

    /**
     * Will get posts that are not yet saved in Magento
     *
     * @param array $remotePosts
     * @param $storeId
     * @return array
     */
    protected function getNewPosts(array $remotePosts, $storeId): array
    {
        $postIds = [];
        // if it is a single post
        if (isset($remotePosts['id'])) {
            $postIds[] = $remotePosts['id'];
        } else {
            foreach ($remotePosts as $remotePost) {
                $postIds[] = $remotePost['id'];
            }
        }

        $postCollection = $this->wordPressPostCollectionFactory->create();
        $existingPosts = $postCollection
            ->addFieldToSelect(WordPressPostResource::POST_ID_FIELD)
            ->addFieldToFilter(WordPressPostResource::POST_ID_FIELD, ['in' => $postIds])
            ->addFieldToFilter(WordPressPostResource::STORE_ID_FIELD, ['eq' => $storeId])
            ->getItems();

        $existingPostIds = [];
        foreach ($existingPosts as $existingPost) {
            $existingPostIds[] = $existingPost->getData(WordPressPostResource::POST_ID_FIELD);
        }

        $newPosts = [];
        // if it is a single post
        if (isset($remotePosts['id'])) {
            if (!in_array((string)$remotePosts['id'], $existingPostIds, true)) {
                $newPosts[] = $remotePosts;
            }
        } else {
            foreach ($remotePosts as $remotePost) {
                if (!in_array((string)$remotePost['id'], $existingPostIds, true)) {
                    $newPosts[] = $remotePost;
                }
            }
        }

        return $newPosts;
    }

    /**
     * Will get blog posts and save new ones in the DB
     * @param $blogsHost
     * @param $storeId
     * @param string $customPostsUrl - custom url passed from cli command to get legacy posts
     * @throws AlreadyExistsException
     */
    public function getBlogPosts($blogsHost, $storeId, string $customPostsUrl = '')
    {
        if (empty($customPostsUrl)) {
            $remotePosts = json_decode($this->getRemoteData($this->getPostsApiUrl($blogsHost)), true);
        } else {
            $remotePosts = json_decode($this->getRemoteData($customPostsUrl), true);
        }

        $postsToProcess = $this->getNewPosts($remotePosts, $storeId);

        foreach ($postsToProcess as $post) {
            $postMedia = $this->getPostMedia($post);
            $postEmbed = $this->getPostEmbed($post);

            if (empty($postEmbed)) {
                $postEmbed = $this->getPostEmbeddedVideo($post);
            }

            $postCategory = $this->getPostCategory($post);

            $postToSave = $this->wordPressPostFactory->create();
            $postToSave->setPostId($post['id'])
                ->setStoreId($storeId)
                ->setDateGmt($post['date_gmt'])
                ->setLink($post['link'])
                ->setTitle($post['title']['rendered'])
                ->setExcerpt($post['excerpt']['rendered'])
                ->setImageUrl($postMedia[0])
                ->setVideoUrl($postMedia[1])
                ->setVideoEmbed($postEmbed)
                ->setCategoryLabel($postCategory[0])
                ->setCategoryUrl($postCategory[1]);

            $this->wordPressPostResource->save($postToSave);
        }
    }
}

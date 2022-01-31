# ScandiPWA_WordPressIntegration

* Will add config to specify source blog to get posts from.
* Will use cron to get blog post data
* Will add widget to select blog posts for CMS page
* Will show blog post cards on a CMA page

## Installation

To install dependencies for the module run

```
composer require scandipwa/installer
composer require scandipwa/customization

```

## Notes

To get all legacy posts, you can use this cli command `magento wordpress:get-posts` and pass a custom url to it

`https://some-site.com/wp-json/wp/v2/posts?page=2` - This will download posts from page 2

`https://some-site.com/wp-json/wp/v2/posts?per_page=100` - This will download first 100 posts

`https://some-site.com/wp-json/wp/v2/posts/123` - This will download post with id 123

More info on WordPress API https://developer.wordpress.org/rest-api/reference/#rest-api-developer-endpoint-reference


Example usage of command

`magento wordpress:get-posts --url=https://some-site.com/wp-json/wp/v2/posts?page=2 --store-id=1`

Manual cron execution

`bin/n98-magerun2.phar sys:cron:run wordpress_get_posts`

or

`magento cron:run --group=wordpress`


## Future improvements
* Add support for selectable post image size. Currently, 'large' image size will be downloaded
* Add support for full blog post content

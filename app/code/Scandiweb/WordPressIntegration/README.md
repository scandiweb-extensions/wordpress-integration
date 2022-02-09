# Scandiweb_WordPressIntegration

Module to get WordPress blog posts and display them in Magneto CMS pages. Blog posts to CMS pages
are added as widgets so this will work in all sorts on Magento setups, like with Luma or any other
Magento theme or with some ScandiPWA theme

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

To get cron installed run

```
magento cache:clean
magento cron:run
magento cron:run
```

## Configuration and usage

**Integration Settings**

To add settings where should Magento get WordPress blog articles for each store go to
**Stores → Configuration → ScandiPWA → WordPress Integration** and save source Url.
Use URL of main url of the blog

New posts from blog will be pulled to Magento every night.


**Blog post post display**

To see blog posts on a CMA page open this page in the admin f.e.
Content → Pages →  Homepage

Create a new **Row** and add **Columns** to it if necessary using Page builder tools

Create a new **Text** element and select **Insert Widget**

As Widget Type select WordPress Post and select a post.

## How to change template used to render blog posts?

To change template use example module from `app/code`.
* Copy it to your project
* Rename `MyVendorName` folder to match your project name or vendor
* Replace in all files `MyVendorName` with you project name or vendor
* Adjust template currently in `app/code/MyVendorName/WordPressIntegration/view/frontend/templates/widget/wordpress_post.phtml`

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
* Add last cron error and execution time to be visible in WordPress settings section
* Add support for selectable post image size. Currently, 'large' image size will be downloaded
* Add support for full blog post content

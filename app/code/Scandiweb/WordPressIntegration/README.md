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

## Module structure

You can find the following functionality implemented in this module

* `etc/widget.xml` Widget configuration. It defines widget layout and custom icon use will see in CMA elements in tha Magento admin

* `view/adminhtml/web/wordpress_post.png` Custom icon for widget element in Magento admin so users can distinguish among different widgets that are added to page

* `view/frontend/templates/widget/wordpress_post.phtml` Magento template file used to render widget content for frontend

* `Block/Widget/WordPressPost.php` Widget block class with a few useful functions for rendering the posts content. These functions are used in `.phtml` template

* `Cron/GetBlogPosts.php` Cron command implementation to get WordPres posts

* `Console/Command/GetWordPressPosts.php` Magento cli command implementation that will process cli parameters adn will call a function from `GetBlogPosts` cron to get blog posts

* `Model` Here you will find classes that implement WordPress posts so Magento can store them in the DB and can work with them

* `Controller/Adminhtml/Block/Widget/Chooser.php` This file is responsible for first widget popup and will load info about selected WordPress post if we are editing some widget in the admin

* `Block/Adminhtml/Widget/Chooser.php` This class is responsible for WordPress post selection table. It has functions to prepare and load WordPress post collection and js functions to handle user click on some post to select it.

* `Block/Adminhtml/Grid/Column` Code to process image and video columns, so they look nicer in the admin grid.

## Future improvements
* Add last cron error and execution time to be visible in WordPress settings section
* Add support for selectable post image size. Currently, 'large' image size will be downloaded
* Add support for full blog post content

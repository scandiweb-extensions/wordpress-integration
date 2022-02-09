<?php
namespace MyVendorName\WordPressIntegration\Plugin;

class OverrideWordPressPostTemplate
{
    public function beforeToHtml(\Scandiweb\WordPressIntegration\Block\Widget\WordPressPost $subject)
    {
        if ($subject->getTemplate() === 'Scandiweb_WordPressIntegration::widget/wordpress_post.phtml') {
            $subject->setTemplate('MyVendorName_WordPressIntegration::widget/wordpress_post.phtml');
        }
    }
}

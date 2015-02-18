<?php

/**
* The dashboard-specific functionality of the plugin.
*
* @link       https://github.com/japorito/SimplePie-WordPress-Plugin
* @since      0.0.1
*
* @package    WordPress_Pie
* @subpackage WordPress_Pie/public
*/

/**
* Defines and registers the "rss" shortcode.
*
* @package    WordPress_Pie
* @subpackage WordPress_Pie/public
* @author     Jacob Saporito <japorito@gmail.com>
*/
class RSS_ShortCode implements ShortCode {
    public function run($options) {
        $opts = shortcode_atts(array(
            'url' => 'invalid',
            'cache' => 'false'
        ), $options);

        $feed = new SimplePie();
        $feed->set_feed_url($opts['url']);

        //disable cache if set to false, if cache is set to true, leave as default
        //(cached), finally if set to neither 'true' nor 'false', use this parameter
        //to specify cache directory.
        if ($opts['cache'] == 'false') {
            $feed->enable_cache(false);
        }
        else if ($opts['cache'] != 'true') {
            $feed->set_cache_location($opts['cache']);
        }

        $feed->init();

        ob_start();

        foreach ($feed->get_items as $item) {
            ?>
            <div class="item">
                <h2><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a></h2>
                <p><?php echo $item->get_description(); ?></p>
                <p><small>Posted on <?php echo $item->get_date('j F Y | g:i a'); ?></small></p>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    public function get_tag() {
        return 'rss';
    }
}

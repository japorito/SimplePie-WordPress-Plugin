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
    public static function run($options) {
        $opts = shortcode_atts(array(
            'url' => 'invalid',
            'cache' => 'false',
            'cachelocation' => WP_CONTENT_DIR . '/cache',
            'cacheduration' => '300'
        ), $options);

        $feed = new SimplePie();
        $feed->set_feed_url($opts['url']);

        //disable cache if set to false, otherwise leave caching enabled
        if ($opts['cache'] == 'false') {
            $feed->enable_cache(false);
        }
        else {
            $feed->set_cache_location($opts['cachelocation']);
            $feed->set_cache_duration((int) $opts['cacheduration']);
        }

        $feed->init();

        ob_start();

        foreach ($feed->get_items() as $item) {
            ?>
            <div class="wppie item">
                <h2><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a></h2>
                <?php
                foreach ($item->get_enclosures() as $enclosure) {
                    if (preg_match('/^audio/', $enclosure->get_type())) {
                        echo "<p><audio src=\"" . $enclosure->get_link() . "\" controls preload=\"none\"></audio></p>";
                    }
                    else if (preg_match('/^video/', $enclosure->get_type())) {
                        echo "<p><video src=\"" . $enclosure->get_link() . "\" controls preload=\"none\"></video></p>";
                    }
                }
                ?>
                <p><?php echo $item->get_description(); ?></p>
                <p><small>Posted on <?php echo $item->get_date('j F Y | g:i a'); ?></small></p>
            </div>
            <?php
        }

        echo "<div class=\"pager\"></div>";

        return ob_get_clean();
    }

    public static function get_tag() {
        return 'wppie';
    }
}

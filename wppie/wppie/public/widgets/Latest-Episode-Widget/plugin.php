<?php
/**
 * WordPress Widget Boilerplate
 *
 * The WordPress Widget Boilerplate is an organized, maintainable boilerplate for building widgets using WordPress best practices.
 *
 * @package   Latest_Episode_Widget
 * @author    Jacob Saporito <japorito@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/japorito/
 * @copyright 2014 Jacob Saporito
 *
 * @wordpress-plugin
 * Plugin Name:       Wordpress Pie
 * Plugin URI:        https://github.com/japorito/SimplePie-WordPress-Plugin/
 * Description:       This plugin reads and displays RSS feeds. This widget is for podcasts; it shows the latest episode in an audio tag.
 * Version:           0.0.1
 * Author:            Jacob Saporito
 * Author URI:        https://github.com/japorito/
 * Text Domain:       latest-episode-widget
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /lang
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */

 // Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
  exit;
}

class Latest_Episode_Widget extends WP_Widget {

    /**
     * Unique identifier for your widget.
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    0.0.1
     *
     * @var      string
     */
    protected $widget_slug = 'latest-episode-widget';

  /*--------------------------------------------------*/
  /* Constructor
  /*--------------------------------------------------*/

  /**
   * Specifies the classname and description, instantiates the widget,
   * loads localization files, and includes necessary stylesheets and JavaScript.
   */
  public function __construct() {

    // load plugin text domain
    add_action( 'init', array( $this, 'widget_textdomain' ) );

    // Hooks fired when the Widget is activated and deactivated
    register_activation_hook( __FILE__, array( $this, 'activate' ) );
    register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

    parent::__construct(
      $this->get_widget_slug(),
      __( 'Latest Episode Widget', $this->get_widget_slug() ),
      array(
        'classname'  => $this->get_widget_slug().'-class',
        'description' => __( 'Displays the latest episode in a wordpress widget.', $this->get_widget_slug() )
      )
    );

    // Register admin styles and scripts
    add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

    // Register site styles and scripts
    add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

    // Refreshing the widget's cached output with each new post
    add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
    add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
    add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

  } // end constructor


    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

  /*--------------------------------------------------*/
  /* Widget API Functions
  /*--------------------------------------------------*/

  /**
   * Outputs the content of the widget.
   *
   * @param array args  The array of form elements
   * @param array instance The current instance of the widget
   */
  public function widget( $args, $instance ) {


    // Check if there is a cached output
    $cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

    if ( !is_array( $cache ) )
      $cache = array();

    if ( ! isset ( $args['widget_id'] ) )
      $args['widget_id'] = $this->id;

    if ( isset ( $cache[ $args['widget_id'] ] ) )
      return print $cache[ $args['widget_id'] ];

    // go on with your widget logic, put everything into a string and …

    $rss_icon = plugin_dir_url( __FILE__ ) . 'images/icon-rss.png';
    $feedly_icon = plugin_dir_url( __FILE__ ) . 'images/icon-feedly.png';
    $itunes_icon = plugin_dir_url( __FILE__ ) . 'images/icon-itunes.png';

    $feed = new SimplePie();
    $feed->set_feed_url($instance['feed']);
    $feed->set_cache_location('wpdb://localhost');
    $feed->set_cache_duration(300);

    $feed->init();

    $item = $feed->get_item(0); //get latest item

    extract( $args, EXTR_SKIP );

    $widget_string = $before_widget;

    ob_start();
    include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );
    $widget_string .= ob_get_clean();
    $widget_string .= $after_widget;


    $cache[ $args['widget_id'] ] = $widget_string;

    wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

    print $widget_string;

  } // end widget


  public function flush_widget_cache()
  {
      wp_cache_delete( $this->get_widget_slug(), 'widget' );
  }
  /**
   * Processes the widget's options to be saved.
   *
   * @param array new_instance The new instance of values to be generated via the update.
   * @param array old_instance The previous instance of values before the update.
   */
  public function update( $new_instance, $old_instance ) {
    $instance = array(
      'feed' => empty($new_instance['feed']) ? $old_instance['feed'] : $new_instance['feed'],
      'itunessub' => $new_instance['itunessub'],
      'alleps' => $new_instance['alleps']
    );

    return $instance;
  } // end widget

  /**
   * Generates the administration form for the widget.
   *
   * @param array instance The array of keys and values for the widget.
   */
  public function form( $instance ) {

    $instance = wp_parse_args(
      (array) $instance,
      array(
        'feed' => 'Not Set',
        'itunessub' => '',
        'alleps' => ''
      )
    );

    $feedUrl = $instance['feed'];
    $feed_input_name = $this->get_field_name('feed');
    $feed_input_id = $this->get_field_id('feed');
    $iTunes = $instance['itunessub'];
    $itunessub_input_name = $this->get_field_name('itunessub');
    $itunessub_input_id = $this->get_field_id('itunessub');
    $allEpisodes = $instance['alleps'];
    $alleps_input_name = $this->get_field_name('alleps');
    $alleps_input_id = $this->get_field_id('alleps');

    // Display the admin form
    include( plugin_dir_path(__FILE__) . 'views/admin.php' );

  } // end form

  /*--------------------------------------------------*/
  /* Public Functions
  /*--------------------------------------------------*/

  /**
   * Loads the Widget's text domain for localization and translation.
   */
  public function widget_textdomain() {

    load_plugin_textdomain( $this->get_widget_slug(), false, plugin_dir_path( __FILE__ ) . 'lang/' );

  } // end widget_textdomain

  /**
   * Fired when the plugin is activated.
   *
   * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
   */
  public function activate( $network_wide ) {
    // TODO define activation functionality here
  } // end activate

  /**
   * Fired when the plugin is deactivated.
   *
   * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
   */
  public function deactivate( $network_wide ) {
    // TODO define deactivation functionality here
  } // end deactivate

  /**
   * Registers and enqueues admin-specific styles.
   */
  public function register_admin_styles() {

    wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

  } // end register_admin_styles

  /**
   * Registers and enqueues admin-specific JavaScript.
   */
  public function register_admin_scripts() {

    wp_enqueue_script( $this->get_widget_slug().'-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array('jquery') );

  } // end register_admin_scripts

  /**
   * Registers and enqueues widget-specific styles.
   */
  public function register_widget_styles() {

    wp_enqueue_style( $this->get_widget_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

  } // end register_widget_styles

  /**
   * Registers and enqueues widget-specific scripts.
   */
  public function register_widget_scripts() {

    wp_enqueue_script( $this->get_widget_slug().'-script', plugins_url( 'js/widget.js', __FILE__ ), array('jquery') );

  } // end register_widget_scripts

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("Latest_Episode_Widget");' ) );

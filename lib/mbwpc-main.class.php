<?php

/**
 * MB Calendar - a versatile and powerful calendar/event system for your WordPress website.
 *
 * @package MB_Calendar
 * @author Marian Bucur <thebigman@marianbucur.com>
 * @version 1.0
 * @copyright Marian Bucur
 */

// Disable loading the file directly.
if ( !defined('ABSPATH') )
  die('Don\'t load this file directly!');

require('mbwpc-base.class.php');
require('mb-calendar.class.php');

/**
 * The main class.
 *
 * @package MB_Calendar
 */
class MBWPC_Main extends MBWPC_Base
{

	/**
   * The class instance.
   *
   * @var MBWPC_Main
   * @access private
   * @static
   */
	private static $instance;

	/**
   * The class constructor.
   *
   * @access protected
   */
  private function __construct()
  {
		// Call the parent class constructor.
		parent::__constructor();

		// Check if the requirements are satisfied.
    if ( self::is_supported('wp') && self::is_supported('php') )
		{
			require(self::$plugin_path . 'lib/mbwpc-admin-calendar.class.php');
			require(self::$plugin_path . 'lib/mbwpc-admin-event.class.php');

			if ( !is_admin() )
				require(self::$plugin_path . 'lib/mbwpc-calendar.class.php');

			// Add actions.
			add_action( 'admin_enqueue_scripts', array(__CLASS__, 'add_admin_media') );

			// Add filters
			add_filter( 'plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2 );
    }
		else
		{
			// Add an error message if the requirements were not satisfied.
      add_action( 'admin_head', array(__CLASS__, 'not_supported_error') );
    }
  }

	/**
   * Get the class instance.
   *
   * @access public
   * @static
   * @return MBWPC_Main
   */
	public static function get_instance()
  {
    if ( !isset(self::$instance) )
		{
			$class = __CLASS__;
      self::$instance = new $class;
    }

    return self::$instance;
  }

	/**
   * Enqueu the required css and js.
   *
   * @access public
   * @static
   */
	public static function add_admin_media()
	{
		if ( !self::check_post_type() )
			return;

		wp_enqueue_style('mbwpc-admin-style', self::$plugin_url . 'media/css/admin-style.css');
		wp_enqueue_style('mbwpc-jquery.ui.theme', self::$plugin_url . 'media/css/smoothness/jquery-ui.css');
		wp_enqueue_script( 'mbwpc-admin-script', self::$plugin_url . 'media/js/admin-script.js', array('jquery-ui-datepicker') );
	}

	/**
	 * Add some more links to the plugin row meta.
	 *
	 * @param array $links Already defined links
	 * @param string $file File path
	 * @return array
	 */
	public static function plugin_row_meta($links, $file)
	{
		if ($file == 'mb-wordpress-calendar/mb-wordpress-calendar.php')
			$links[] = '<a href="http://codecanyon.net/item/mb-wordpress-calendar/2585635?rel=g0dlik3" title="Get the PRO version!">' . __('Upgrade') . '</a>';

		return $links;
	}

	/**
   * Check if the requirements are satisfied.
   *
   * @param string $system
   * @access public
   * @static
   * @return bool
   */
  private static function is_supported($system)
  {

		$system = strtolower($system);

    if ( !$is_supported = wp_cache_get($system, 'mbwpc_version_test') )
		{
			if ($system == 'php')
				$is_supported = version_compare( phpversion(), self::SUPPORTED_PHP_VERSION, '>=' );
			else if ($system == 'wp')
				$is_supported = version_compare( get_bloginfo('version'), self::SUPPORTED_WP_VERSION, '>=' );

      wp_cache_set($system, $is_supported, 'mbwpc_version_test');
    }

    return $is_supported;
  }

	/**
   * Display an error message if the requirements are not satisfied.
   *
   * @access public
   * @static
   */
  public static function not_supported_error()
  {
    if ( !self::is_supported('php') )
      echo '<div class="error"><p>' , sprintf('%s requires PHP %s or higher.', self::PLUGIN_NAME, self::SUPPORTED_PHP_VERSION) , '</p></div>';
    if ( !self::is_supported('wp') )
      echo '<div class="error"><p>' , sprintf('%s requires WordPress %s or higher. Please update your WordPress install.', self::PLUGIN_NAME, self::SUPPORTED_WP_VERSION) , '</p></div>';
  }
}

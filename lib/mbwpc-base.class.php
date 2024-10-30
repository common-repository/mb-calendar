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

/**
 * The base class.
 *
 * @package MB_Calendar
 */
abstract class MBWPC_Base
{
  /**
   * The name of the plugin.
   */
  const PLUGIN_NAME = 'MB Calendar';

  /**
   * The short name of the plugin.
   */
  const PLUGIN_SHORT_NAME = 'MB Calendar';

  /**
   * The plugin version.
   */
  const VERSION = '1.0';

  /**
   * PHP minimum supported version.
   */
  const SUPPORTED_PHP_VERSION = '5.0';

  /**
   * WordPress minimum supported version.
   */
  const SUPPORTED_WP_VERSION = '3.0';

  /**
   * The name of the calendar post type.
   */
  const CALENDAR_POST_TYPE = 'mbwpc_calendar';

  /**
   * The name of the event post type.
   */
  const EVENT_POST_TYPE = 'mbwpc_event';

  /**
   * The name of calendar taxonomy.
   */
  const TAXONOMY = 'mbwpc_calendar_category';

  /**
   * The nonce name.
   */
  const NONCE_NAME = 'mbwpc_nonce';

  /**
   * Shortcode name.
   */
  const SHORTCODE = 'mbwpc';

  /**
   * AJAX action.
   */
  const AJAX_ACTION = 'mbwpc_ajax';

  /**
   * The date format used for validation.
   */
  const DATE_FORMAT = 'n/j/Y';

  /**
   * The time format used for validation.
   */
  const TIME_FORMAT = 'g:i A';

  /**
   * The plugin path.
   *
   * @var string
   * @access protected
   * @static
   */
  protected static $plugin_path;

  /**
   * The plugin dir.
   *
   * @var string
   * @access protected
   * @static
   */
  protected static $plugin_dir;

  /**
   * The plugin url.
   *
   * @var string
   * @access protected
   * @static
   */
  protected static $plugin_url;

  /**
   * The calendar meta.
   *
   * @var array
   * @access protected
   * @static
   */
  protected static $calendar_meta;

  /**
   * The calendar ID.
   *
   * @var string
   * @access protected
   * @static
   */
  protected static $calendar_id;

  /**
   * The priority options.
   *
   * @var string
   * @access protected
   * @static
   */
  protected static $priority_options;

  /**
   * User roles.
   *
   * @var array
   * @access protected
   * @static
   */
  protected static $roles = array(
    'administrator' => array(
      'edit_%s', 'read_%s', 'delete_%s', 'delete_tribe_%ss', 'edit_%ss', 'edit_others_%ss', 'delete_others_%ss',
      'publish_%ss', 'edit_published_%ss', 'delete_published_%ss', 'delete_private_%ss', 'edit_private_%ss', 'read_private_%ss',
    ),

    'editor' => array(
      'edit_%s', 'read_%s', 'delete_%s', 'delete_tribe_%ss', 'edit_%ss', 'edit_others_%ss', 'delete_others_%ss',
      'publish_%ss', 'edit_published_%ss', 'delete_published_%ss', 'delete_private_%ss', 'edit_private_%ss', 'read_private_%ss',
    ),

    'author' => array(
      'edit_%s', 'read_%s', 'delete_%s', 'delete_tribe_%ss', 'edit_%ss',
      'publish_%ss', 'edit_published_%ss', 'delete_published_%ss',
    ),

    'contributor' => array(
      'edit_%s', 'read_%s', 'delete_%s', 'delete_tribe_%ss', 'edit_%ss',
    ),

    'subscriber' => array(
      'read_%s',
    ),
  );

  /**
   * The class constructor.
   *
   * @access protected
   */
  protected function __constructor()
  {
    // Init a few variables.
    self::$plugin_path = trailingslashit( dirname( dirname(__FILE__) ) );
    self::$plugin_dir = trailingslashit( basename(self::$plugin_path) );
    self::$plugin_url = plugins_url(self::$plugin_dir);

    foreach (MB_Calendar::$priorities as $key => $priority)
      self::$priority_options[$key] = $priority['text'];
  }

  /**
   * Add the user capabilities for the $post_type.
   *
   * @param string $post_type
   * @access protected
   * @static
   */
  protected static function add_capabilities($post_type)
  {
    foreach (self::$roles as $role => $capabilities)
    {

      $role_definition = get_role($role);

      foreach ($capabilities as $capability)
        $role_definition->add_cap( sprintf($capability, $post_type) );
    }
  }

  /**
   * Validate the date.
   *
   * @param string $date
   * @access protected
   * @static
   * @return bool
   */
  protected static function validate_date($date)
  {
    return date(self::DATE_FORMAT, strtotime($date)) == $date;
  }

  /**
   * Validate the time.
   *
   * @param string $date
   * @access protected
   * @static
   * @return bool
   */
  protected static function validate_time($time)
  {
    return date(self::TIME_FORMAT, strtotime($time)) == $time;
  }

  /**
   * Configure the MB_Calendar object based on the calendar meta.
   *
   * @param MB_Calendar $calendar
   * @access protected
   * @static
   */
  protected static function configure_calendar(&$calendar)
  {
    // Set the current time.
    $current_time = current_time('timestamp');

		if ( self::get_meta('mbwpc-current-month') )
    {
      $selected_time = $current_time;
    }
    else
    {
      // Set the selected year and month.
      $selected_year = self::get_meta('mbwpc-default-year');
      $selected_month = self::get_meta('mbwpc-default-month');

      if ($selected_year && $selected_month)
        $selected_time = mktime(0, 0, 0, $selected_month, 1, $selected_year);
      else
        $selected_time = $current_time;
    }

    // Set the current time and the selected time.
    $calendar->set_current_time($current_time);
		$calendar->set_selected_time($selected_time);

    // Set the first day of the week.
    if ( $first_day_of_the_week = self::get_meta('mbwpc-first-day-of-the-week') )
      $calendar->set_first_day_of_the_week($first_day_of_the_week);

    // Set the default view.
    $default_view = self::get_meta('mbwpc-default-view') == 0 ? 'calendar' : 'event_list';
    $calendar->set_view($default_view);

    // Show or hide the display change view button.
    if ( $default_view == 'event_list' && self::get_meta('mbwpc-hide-view-change-button') )
      $calendar->set_display_change_view(false);

    // Show or hide change month controls.
    $calendar->set_display_navigation( !self::get_meta('mbwpc-hide-month-change-controls') );

    // Sort by.
    if ( $sort_by = self::get_meta('mbwpc-sort-by') )
      $calendar->set_sort_by( strtolower(MB_Calendar::$sort_by_options[$sort_by]) );

    // Sort.
    if ( $sort = self::get_meta('mbwpc-sort') )
      $calendar->set_sort( strtolower(MB_Calendar::$sort_options[$sort]) );

    // Show or hide the sorting options.
    $calendar->set_display_sort( !self::get_meta('mbwpc-hide-sort') );

    // Set the width of the calendar..
    $calendar->set_width( self::get_meta('mbwpc-width') );
  }

  /**
   * Retrieve the meta value for a meta key.
   *
   * @param string $key
   * @access protected
   * @static
   * @return string
   */
  protected static function get_meta($key)
	{
		if (self::$calendar_meta)
			foreach (self::$calendar_meta as $data)
				if ($data->meta_key == $key)
					return $data->meta_value;
	}

  /**
   * Retrieve the calendars.
   *
   * @access protected
   * @static
   * @return array
   */
  public static function get_calendar_options()
  {
    global $wpdb;

    $calendar_options = array(0 => 'None');

		// Get the calendars from the database.
		$calendars = $wpdb->get_results("
		SELECT `ID`, `post_title`
		FROM `$wpdb->posts`
		WHERE `post_type` = '" . self::CALENDAR_POST_TYPE . "' AND (`post_status` = 'publish' OR `post_status` = 'draft')
		ORDER BY `post_title`");

    // Build the calendar options array.
		foreach ($calendars as $calendar)
			$calendar_options[$calendar->ID] = $calendar->post_title;

    return $calendar_options;
  }

  /**
   * Get the events.
   *
   * @access protected
   * @static
   * @return array
   */
  protected static function get_events()
  {
    global $wpdb;

    $sql = "SELECT `post_title` AS `title`, `post_content` AS `description`,
            `mbwpc_all_day_event`.`meta_value` AS `all_day_event`, `mbwpc_start`.`meta_value` AS `start`, `mbwpc_end`.`meta_value` AS `end`,
            `mbwpc_priority`.`meta_value` AS `priority`, `mbwpc_location`.`meta_value` AS `location`,
            `mbwpc_enable_google_maps`.`meta_value` AS `enable_google_maps`,
            `mbwpc_sort_by`.`meta_value` AS `sort_by`, `mbwpc_sort`.`meta_value` AS `sort`, `mbwpc_hide_sort`.`meta_value` AS `hide_sort`
            FROM `$wpdb->posts`
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_all_day_event` ON `$wpdb->posts`.`ID` = `mbwpc_all_day_event`.`post_id` AND `mbwpc_all_day_event`.`meta_key` = 'mbwpc-all-day-event'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_start` ON `$wpdb->posts`.`ID` = `mbwpc_start`.`post_id` AND `mbwpc_start`.`meta_key` = 'mbwpc-start'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_end` ON `$wpdb->posts`.`ID` = `mbwpc_end`.`post_id` AND `mbwpc_end`.`meta_key` = 'mbwpc-end'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_priority` ON `$wpdb->posts`.`ID` = `mbwpc_priority`.`post_id` AND `mbwpc_priority`.`meta_key` = 'mbwpc-priority'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_location` ON `$wpdb->posts`.`ID` = `mbwpc_location`.`post_id` AND `mbwpc_location`.`meta_key` = 'mbwpc-location'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_enable_google_maps` ON `$wpdb->posts`.`ID` = `mbwpc_enable_google_maps`.`post_id` AND `mbwpc_enable_google_maps`.`meta_key` = 'mbwpc-enable-google-maps'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_sort_by` ON `$wpdb->posts`.`ID` = `mbwpc_sort_by`.`post_id` AND `mbwpc_sort_by`.`meta_key` = 'mbwpc-sort-by'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_sort` ON `$wpdb->posts`.`ID` = `mbwpc_sort`.`post_id` AND `mbwpc_sort`.`meta_key` = 'mbwpc-sort'
            LEFT JOIN `$wpdb->postmeta` AS `mbwpc_hide_sort` ON `$wpdb->posts`.`ID` = `mbwpc_hide_sort`.`post_id` AND `mbwpc_hide_sort`.`meta_key` = 'mbwpc-hide-sort'
            WHERE `post_status` = 'publish' AND `post_type` = %s AND `post_parent` = %d";

    return $wpdb->get_results( $wpdb->prepare($sql, self::EVENT_POST_TYPE, self::$calendar_id), ARRAY_A );
  }

  /**
   * Get the current post type.
   *
   * @access protected
   * @static
   * @return mixed
   */
  protected static function get_current_post_type()
  {
    global $post, $typenow, $current_screen;

    if ($post && $post->post_type)
      return $post->post_type;
    else if( $typenow )
      return $typenow;
    else if ($current_screen && $current_screen->post_type)
      return $current_screen->post_type;
    elseif( isset($_REQUEST['post_type']) )
      return sanitize_key($_REQUEST['post_type']);

    return null;
  }

  /**
   * Check if the post type belongs to the plugin.
   *
   * @access protected
   * @static
   * @return boolean
   */
  protected static function check_post_type()
  {
    $post_type = self::get_current_post_type();

    return $post_type == self::CALENDAR_POST_TYPE || $post_type == self::EVENT_POST_TYPE;
  }
}

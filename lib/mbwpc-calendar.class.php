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
 * The used for displaying the calendar/event list on a post/page.
 *
 * @package MB_Calendar
 */
class MBWPC_Calendar extends MBWPC_Base
{
	/**
   * The class instance.
   *
   * @var MBWPC_Calendar
   * @access private
   * @static
   */
  private static $instance;

	/**
   * Load the media only if there is a calendar shortcode in the post/page content.
   *
   * @var bool
   * @access private
   * @static
   */
	private static $load_media;

	/**
   * The MB_Calendar object.
   *
   * @var MB_Calendar
   * @access private
   * @static
   */
	private static $calendar;

	/**
   * The class constructor.
   *
   * @access protected
   */
  protected function __construct()
  {
		// Call the parent class constructor.
    parent::__constructor();

		// Add a handle for the mbwpc shortcode.
		add_shortcode( self::SHORTCODE, array(__CLASS__, 'process_shortcode') );
    add_action( 'wp_footer', array(__CLASS__, 'print_scripts') );
  }

	/**
   * Get the class instance.
   *
   * @access public
   * @static
   * @return MBWPC_Calendar
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
   * The shortcode handle.
   *
   * @param array
   * @access public
   * @static
   * @return string
   */
  public static function process_shortcode($atts)
  {
		// Enable only one calendar per page.
		if (self::$calendar_id)
			return;

		// Extract the attributes to variables.
    extract( shortcode_atts( array('id' => null), $atts ) );

		// Check if the extacted $id is numeric.
    if ( $id && is_numeric($id) )
		{
      global $wpdb;

			// Check if the calendar exists and is published.
      if ( $wpdb->get_var( $wpdb->prepare("SELECT `ID` FROM `$wpdb->posts` WHERE `ID` = %d AND `post_status` = 'publish'", $id) ) )
			{
				// Add the media only if we are displaying the calendar.
				wp_enqueue_style('mbwpc-style', self::$plugin_url . 'media/css/style.css');
				wp_enqueue_script( 'mbwpc-script', self::$plugin_url . 'media/js/script.js', array('jquery') );

				// Set the calendar id.
				self::$calendar_id = $id;

				// Load the rest of the necessary media.
				self::$load_media = true;

				// Get the settings for the calendar.
        self::$calendar_meta = $wpdb->get_results( $wpdb->prepare("SELECT `meta_key`, `meta_value` FROM `$wpdb->postmeta` WHERE `post_id` = %d AND `meta_key` LIKE 'mbwpc-%%'", $id) );

				// Create a new MB_Calendar object.
        $calendar = new MB_Calendar;

				// Configure the calendar.
				self::configure_calendar($calendar);

				// Add the events.
				$calendar->add_events( self::get_events() );

				// Save the MB_Calendar object in a class variable.
				self::$calendar = $calendar;

				// Generate and return the calendar.
        return $calendar->generate();
      }
    }
  }

	/**
   * Print some JS code.
   *
   * @access public
   * @static
   */
  public static function print_scripts()
  {
		if (!self::$load_media)
			return;

		// Get the current year, month and day.
    list($current_year, $current_month, $current_day) = explode( ' ', date( 'Y m d', current_time('timestamp') ) );

		if ( self::get_meta('mbwpc-current-month') )
		{
			$selected_year = $current_year;
			$selected_month = $current_month;
		}
		else
		{
			$selected_year = self::get_meta('mbwpc-default-year');
			$selected_month = self::get_meta('mbwpc-default-month');

			if ($selected_year === null || $selected_month === null)
			{
				$selected_year = $current_year;
				$selected_month = $current_month;
			}
		}

		// Decrement the month, because in javascript, months range from 0 to 11 :\
    --$current_month;
		--$selected_month;

		// Get the MB_Calendar object.
		$calendar = self::$calendar;

		// Get the months.
		$months = $calendar->get_months();

		// Output the script.
    echo "<script type=\"text/javascript\">
    /* <![CDATA[ */
			var MBC = {
				id: " . self::$calendar_id . ",
				current_date: new Date($current_year, $current_month, $current_day),
				selected_date: new Date($selected_year, $selected_month, 1),
				months: ["; foreach ($months as $month) echo "'$month',"; echo  "],
				change_view: {calendar: 'Calendar', event_list: 'Event List',},
				ajax_url: '" . admin_url('admin-ajax.php') . "',
				ajax_delay: 500,
				ajax_action: '" . self::AJAX_ACTION . "',
				nonce: '" . wp_create_nonce(self::AJAX_ACTION) . "',
			}
    /* ]]> */</script>\n";
  }
}

MBWPC_Calendar::get_instance();

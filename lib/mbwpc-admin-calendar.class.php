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
 * The calendars admin section.
 *
 * @package MB_Calendar
 */
class MBWPC_Admin_Calendar extends MBWPC_Base
{
	/**
   * The class instance.
   *
   * @var MBWPC_Admin_Calendar
   * @access private
   * @static
   */
  private static $instance;

	/**
   * The calendars cache.
   *
   * @var array
   * @access private
   * @static
   */
	private static $calendars;

	/**
   * The view options.
   *
   * @var array
   * @access private
   * @static
   */
	private static $view_options = array(
		0 => 'Calendar',
		1 => 'Event List',
	);

	/**
   * The class constructor.
   *
   * @access protected
   */
  protected function __construct()
  {
		// Call the parent class constructor.
    parent::__constructor();

		// Always add the init action.
		add_action( 'init', array(__CLASS__, 'init') );

		// Add the following actions and filters only if we are in the admin area.
		if ( is_admin() )
		{
			// Add actions.
			add_action( 'add_meta_boxes', array(__CLASS__, 'add_meta_box') );
			add_action( 'save_post', array(__CLASS__, 'save_post'), 15, 2 );
			add_action( 'manage_posts_custom_column', array(__CLASS__, 'custom_columns'), 10, 2 );
			add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array(__CLASS__, 'ajax') );
			add_action( 'wp_ajax_' . self::AJAX_ACTION, array(__CLASS__, 'ajax') );

			// Add filters.
			add_filter( 'manage_' . self::CALENDAR_POST_TYPE . '_posts_columns', array(__CLASS__, 'column_headers') );
			add_filter( 'posts_results',  array(__CLASS__, 'cache_posts_results') );
			add_filter( 'post_row_actions', array(__CLASS__, 'post_row_actions') );
			add_filter( 'post_updated_messages', array(__CLASS__, 'post_updated_messages') );
		}
  }

	/**
   * Get the class instance.
   *
   * @access public
   * @static
   * @return MBWPC_Admin_Calendar
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
   * The init action callback function.
   *
   * @access public
   * @static
   */
	public static function init()
	{
    self::register_post_type();
		self::add_capabilities(self::CALENDAR_POST_TYPE);
	}

	/**
   * Register the custom post type.
   *
   * @access private
   * @static
   */
  private static function register_post_type()
  {
    $post_type_args = array(
      'labels' => array(
        'name' => 'Calendars',
        'singular_name' => 'Calendar',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Calendar',
        'edit_item' => 'Edit Calendar',
        'new_item' => 'New Calendar',
        'view_item' => 'View Calendar',
        'search_items' => 'Search Calendars',
        'not_found' => 'No calendars found',
        'not_found_in_trash' => 'No calendars found in Trash',
      ),
      'public' => true,
      'rewrite' => array('with_front' => false),
      'menu_position' => 5,
      'supports' => array('title', 'editor', 'author'),
      'capability_type' => array('mbwpc_calendar', 'mbwpc_calendars'),
      'map_meta_cap' => true,
    );

		register_post_type(self::CALENDAR_POST_TYPE, $post_type_args);
  }

	/**
   * Add the meta box.
   *
   * @access public
   * @static
   */
  public static function add_meta_box()
  {
		add_meta_box( 'mbwpc-calendar-details', self::PLUGIN_NAME, array(__CLASS__, 'calendar_meta_box'), self::CALENDAR_POST_TYPE, 'normal', 'high' );
  }

	/**
   * The meta box callback function.
   *
   * @access public
   * @static
   */
  public static function calendar_meta_box($post)
  {
		if ( isset($_POST['mbwpc-calendar']) )
		{
			$values = array(
				'current-month' => $_POST['mbwpc-current-month'],
				'default-month' => $_POST['mbwpc-default-month'],
				'default-year' => $_POST['mbwpc-default-year'],
				'first-day-of-the-week' => $_POST['mbwpc-first-day-of-the-week'],
				'default-view' => $_POST['mbwpc-default-view'],
				'hide-view-change-button' => isset($_POST['mbwpc-hide-view-change-button']),
				'hide-month-change-controls' => isset($_POST['mbwpc-month-view-change-controls']),
				'sort-by' => $_POST['mbwpc-sort-by'],
				'sort' => $_POST['mbwpc-sort'],
				'hide-sort' => isset($_POST['mbwpc-hide-sort']),
				'width' => $_POST['mbwpc-width'],
			);
		}
		else
		{
			if ($post && $post->post_status != 'auto-draft')
			{
				$current_month = get_post_meta($post->ID, 'mbwpc-current-month', true);
				$default_month = get_post_meta($post->ID, 'mbwpc-default-month', true);
				$default_year = get_post_meta($post->ID, 'mbwpc-default-year', true);

				if (!$default_month || !$default_year)
					list($default_month, $default_year) = explode( ' ', date( 'n Y', current_time('timestamp') ) );

				$values = array(
					'current-month' => $current_month,
					'default-month' => $default_month,
					'default-year' => $default_year,
					'first-day-of-the-week' => get_post_meta($post->ID, 'mbwpc-first-day-of-the-week', true),
					'default-view' => get_post_meta($post->ID, 'mbwpc-default-view', true),
					'hide-view-change-button' => get_post_meta($post->ID, 'mbwpc-hide-view-change-button', true),
					'hide-month-change-controls' => get_post_meta($post->ID, 'mbwpc-hide-month-change-controls', true),
					'sort-by' => get_post_meta($post->ID, 'mbwpc-sort-by', true),
					'sort' => get_post_meta($post->ID, 'mbwpc-sort', true),
					'hide-sort' => get_post_meta($post->ID, 'mbwpc-hide-sort', true),
					'width' => (int)get_post_meta($post->ID, 'mbwpc-width', true),
				);
			}
			else
			{
				list($month, $year) = explode( ' ', date( 'n Y', current_time('timestamp') ) );

				$values = array(
					'current-month' => true,
					'default-month' => $month,
					'default-year' => $year,
					'first-day-of-the-week' => 1,
					'default-view' => 0,
					'hide-view-change-button' => false,
					'hide-month-change-controls' => false,
					'sort-by' => 0,
					'sort' => 0,
					'hide-sort' => false,
					'width' => 0,
				);
			}
		}

		// Require the view file.
    require(self::$plugin_path . 'admin-views/calendar-meta-box.php');
  }

	/**
   * The save post callback function.
   *
   * @param int $post_id
   * @param mixed $post
   * @access public
   * @static
   */
	public static function save_post($post_id, $post)
	{
		global $wpdb;

		// Do not proceed if it is not the correct post type.
		if ( $post->post_type != self::CALENDAR_POST_TYPE || defined('DOING_AJAX') )
			return;

		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') )
			return;

		// Check the nonce.
		if( !isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce($_POST[self::NONCE_NAME], self::CALENDAR_POST_TYPE) )
			return;

		// The user does not have the required access.
		if ( !current_user_can('publish_posts') )
			return;


		// Delete all the meta for this post that was added by the plugin.
		$wpdb->query( $wpdb->prepare("DELETE FROM `$wpdb->postmeta` WHERE `post_id` = %d AND `meta_key` LIKE 'mbwpc-%%'", $post_id) );

		$current_month = isset($_POST['mbwpc-current-month']);

		// Set the default selected month.
		$default_month = isset($_POST['mbwpc-default-month']) ? ltrim($_POST['mbwpc-default-month'], '0') : null;

		if ( $default_month && isset(MB_Calendar::$months[$default_month]) &&
					isset($_POST['mbwpc-default-year']) && is_numeric($_POST['mbwpc-default-year']) && $_POST['mbwpc-default-year'] > 0 )
		{
			// Set the selected month and year.
			add_post_meta($post_id, 'mbwpc-default-month', $default_month);
			add_post_meta($post_id, 'mbwpc-default-year', $_POST['mbwpc-default-year']);
		}
		else
		{
			$current_month = true;
		}

		// Set the current month as the default selected month.
		if ($current_month)
			add_post_meta($post_id, 'mbwpc-current-month', 1);

		// Set the first day of the week.
		if ( isset($_POST['mbwpc-first-day-of-the-week']) && in_array( $_POST['mbwpc-first-day-of-the-week'], range(1, 7) ) )
			add_post_meta($post_id, 'mbwpc-first-day-of-the-week', $_POST['mbwpc-first-day-of-the-week']);

		// Set the default selected view.
		if ( isset($_POST['mbwpc-default-view']) && in_array( $_POST['mbwpc-default-view'], array(0, 1) ) )
			add_post_meta($post_id, 'mbwpc-default-view', $_POST['mbwpc-default-view']);

		// Hide the view change button.
		if ( isset($_POST['mbwpc-hide-view-change-button']) )
			add_post_meta($post_id, 'mbwpc-hide-view-change-button', 1);

		// Hide the month change controls.
		if ( isset($_POST['mbwpc-hide-month-change-controls']) )
			add_post_meta($post_id, 'mbwpc-hide-month-change-controls', 1);

		// Set the sort by option.
		if ( isset($_POST['mbwpc-sort-by']) && isset(MB_Calendar::$sort_by_options[$_POST['mbwpc-sort-by']]) )
			add_post_meta($post_id, 'mbwpc-sort-by', $_POST['mbwpc-sort-by']);

		// Set the sort by option.
		if ( isset($_POST['mbwpc-sort']) && isset(MB_Calendar::$sort_options[$_POST['mbwpc-sort']]) )
			add_post_meta($post_id, 'mbwpc-sort', $_POST['mbwpc-sort']);

		// Hide the sorting options.
		if ( isset($_POST['mbwpc-hide-sort']) )
			add_post_meta($post_id, 'mbwpc-hide-sort', 1);

		// Set the width.
		if ( isset($_POST['mbwpc-width']) && is_numeric($_POST['mbwpc-width']) && $_POST['mbwpc-width'] > 0 )
			add_post_meta($post_id, 'mbwpc-width', $_POST['mbwpc-width']);
	}

	/**
   * Set the column headers.
   *
   * @param array $columns
   * @access public
   * @static
   * @return array
   */
	public static function column_headers($columns)
	{
		unset($columns['date']);

		$columns_aux = array();
		$i = 0;

		$columns['shortcode'] = 'Shortcode';

		return $columns;
	}

	/**
   * Add the content for the custom columns.
   *
   * @param array $columns
   * @access public
   * @static
   */
	public static function custom_columns($column_id, $post_id)
	{
		if ( get_query_var('post_type') != self::CALENDAR_POST_TYPE )
			return;

		if ($column_id == 'shortcode')
			echo '<input class="mbwpc-shortcode" type="text" value="[' . self::SHORTCODE . ' id=' . $post_id . ']" />';
	}

	/**
   * Cache the post results.
   *
   * @param array $posts
   * @access public
   * @static
   * @return array
   */
	public static function cache_posts_results($posts)
	{
		if ( get_query_var('post_type') == self::CALENDAR_POST_TYPE )
			if ( is_null(self::$calendars) )
				foreach ($posts as $post)
					self::$calendars[$post->ID] = $post;

		return $posts;
	}

	/**
   * Process the post row actions.
   *
   * @param array $actions
   * @access public
   * @static
   * @return array
   */
	public static function post_row_actions($actions)
	{
		if ( get_query_var('post_type') == self::CALENDAR_POST_TYPE )
			unset($actions['view']);

		return $actions;
	}

	/**
   * Process the post update messages.
   *
   * @param array $messages
   * @access public
   * @static
   * @return array
   */
	public static function post_updated_messages($messages)
	{
		$current_screen = get_current_screen();

		if ($current_screen->post_type == self::CALENDAR_POST_TYPE)
			foreach ($messages['post'] as $key => $value)
				$messages['post'][$key] = substr( $value, 0, strpos($value, '.') + 1 );

		return $messages;
	}

	/**
   * Process the AJAX request.
   *
   * @access public
   * @static
   */
	public static function ajax()
	{
		// Verify the none.
    if ( !wp_verify_nonce($_POST['nonce'], self::AJAX_ACTION) )
      die('Naughty, naughty!');

		// Set the header.
		header('Content-Type: text/html');

		// Proceed if the date and calendar id were set and if the calendar id is numeric.
		if ( isset($_POST['date']) && isset($_POST['calendar_id']) && is_numeric($_POST['calendar_id']) )
		{
			list($year, $month, $day) = explode('-', $_POST['date']);

			// Proceed if the date is valid.
			if ( $time = mktime(0, 0, 0, $month, $day, $year) )
			{
				global $wpdb;

				self::$calendar_id = $_POST['calendar_id'];

				// Get the settings for the calendar.
        self::$calendar_meta = $wpdb->get_results( $wpdb->prepare("SELECT `meta_key`, `meta_value` FROM `$wpdb->postmeta` WHERE `post_id` = %d AND `meta_key` LIKE 'mbwpc-%%'", $_POST['calendar_id']) );

				// Create the calendar generator object.
				$calendar = new MB_Calendar;

				// Configure the calendar.
				self::configure_calendar($calendar);

				// Set the selected time.
				$calendar->set_selected_time($time);

				// Add the events.
				$calendar->add_events( self::get_events() );

				// Process the sorting options.
				if ( isset($_POST['sort']) )
				{
					$sort_data = explode( '-', trim( strtolower($_POST['sort']) ) );

					if ( in_array( $sort_data[0], array('date', 'priority', 'title') ) )
						$calendar->set_sort_by($sort_data[0]);

					if (  isset($sort_data[1]) && in_array( $sort_data[1], array('asc', 'desc') ) )
						$calendar->set_sort($sort_data[1]);
				}

				// What to generate.
				if ( isset($_POST['generate']) )
					if ( $_POST['generate'] == 'calendar' )
						echo $calendar->generate_calendar();
					else if ( $_POST['generate'] == 'event_list' )
						echo $calendar->generate_event_list();
					else if ( $_POST['generate'] == 'day_event_list' )
						echo $calendar->generate_event_list( $time, strtotime('tomorrow - 1 second', $time) );
			}
		}

		exit;
	}
}


MBWPC_Admin_Calendar::get_instance();

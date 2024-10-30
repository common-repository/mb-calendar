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
 * The events admin section.
 *
 * @package MB_Calendar
 */
class MBWPC_Admin_Event extends MBWPC_Base
{
	/**
   * The class instance.
   *
   * @var MBWPC_Admin_Event
   * @access private
   * @static
   */
  private static $instance;

	/**
   * The events cache.
   *
   * @var array
   * @access private
   * @static
   */
	private static $events;

	/**
   * The options for the calendar select.
   *
   * @var array
   * @access private
   */
	private static $calendar_options;

	/**
   * The recurrence options.
   *
   * @var array
   * @access private
   * @static
   */
	private static $repeat_options = array(
		0 => 'Do not repeat',
		1 => 'Daily',
		2 => 'Weekly',
		3 => 'Every Weekday',
		4 => 'Monthly',
		5 => 'Yearly',
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
			add_action( 'manage_edit-' . self::EVENT_POST_TYPE . '_sortable_columns', array(__CLASS__, 'sortable_columns'), 10, 2 );
			add_action( 'manage_posts_custom_column', array(__CLASS__, 'custom_columns'), 10, 2 );
			add_action( 'restrict_manage_posts', array(__CLASS__, 'restrict_manage_posts') );

			// Add filters.
			add_filter( 'manage_' . self::EVENT_POST_TYPE . '_posts_columns', array(__CLASS__, 'column_headers') );
			add_filter( 'posts_results',  array(__CLASS__, 'cache_posts_results') );
			add_filter( 'post_row_actions', array(__CLASS__, 'post_row_actions') );
			add_filter( 'tag_row_actions', array(__CLASS__, 'tag_row_actions') );
			add_filter( 'posts_distinct', array(__CLASS__, 'posts_distinct') );
			add_filter( 'posts_fields',	array(__CLASS__, 'posts_fields') );
			add_filter( 'posts_where', array(__CLASS__, 'posts_where') );
			add_filter( 'posts_join', array(__CLASS__, 'posts_join') );
			add_filter( 'posts_orderby',  array(__CLASS__, 'posts_orderby') );
			add_filter( 'post_updated_messages', array(__CLASS__, 'post_updated_messages') );

			// Init the calendar options array.
			self::$calendar_options = self::get_calendar_options();
		}
  }

	/**
   * Get the class instance.
   *
   * @access public
   * @static
   * @return MBWPC_Admin_Event
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
		self::add_capabilities(self::EVENT_POST_TYPE);
		self::register_taxonomy();
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
        'name' => 'Events',
        'singular_name' => 'Event',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Event',
        'edit_item' => 'Edit Event',
        'new_item' => 'New Event',
        'view_item' => 'View Event',
        'search_items' => 'Search Events',
        'not_found' => 'No event found',
        'not_found_in_trash' => 'No event found in Trash',
      ),
      'public' => true,
      'rewrite' => false,
      'menu_position' => 5,
      'supports' => array('title', 'editor', 'author'),
      'capability_type' => array(self::EVENT_POST_TYPE, self::EVENT_POST_TYPE . 's'),
      'map_meta_cap' => true,
    );

		register_post_type(self::EVENT_POST_TYPE, $post_type_args);
  }

	/**
   * Register the taxonomy for the custom post type.
   *
   * @access private
   * @static
   */
	private static function register_taxonomy()
	{
		$taxonomy_args = array(
      'labels' => array(
        'name' =>	'Event Categories',
        'singular_name' =>	'Event Category',
        'search_items' =>	'Search Event Categories',
        'all_items' => 'All Event Categories',
        'parent_item' =>	'Parent Event Category',
        'parent_item_colon' =>	'Parent Event Category:',
        'edit_item' =>	'Edit Event Category',
        'update_item' =>	'Update Event Category',
        'add_new_item' =>	'Add New Event Category',
        'new_item_name' =>	'New Event Category Name',
      ),
      'hierarchical' => true,
      'update_count_callback' => '',
      'rewrite' => false,
			'rewrite' => false,
      'public' => true,
      'show_ui' => true,
      'capabilities' => array(
        'manage_terms' => 'publish_' . self::EVENT_POST_TYPE . 's',
        'edit_terms' => 'publish_' . self::EVENT_POST_TYPE . 's',
        'delete_terms' => 'publish_' . self::EVENT_POST_TYPE . 's',
        'assign_terms' => 'edit_' . self::EVENT_POST_TYPE . 's',
      )
    );

    register_taxonomy(self::TAXONOMY, self::EVENT_POST_TYPE, $taxonomy_args);
	}

	/**
   * Add the meta box.
   *
   * @access public
   * @static
   */
  public static function add_meta_box()
  {
		add_meta_box( 'mbwpc-event-details', self::PLUGIN_NAME, array(__CLASS__, 'event_meta_box'), self::EVENT_POST_TYPE, 'normal', 'high' );
  }

	/**
   * The meta box callback function.
   *
   * @access public
   * @static
   */
  public static function event_meta_box($post)
  {
		if (isset($_POST['mbwpc-calendar']))
		{
			$values = array(
				'calendar' => $_POST['mbwpc-calendar'],
				'all-day-event' => isset($_POST['mbwpc-all-day-event']),
				'start-date' => $_POST['mbwpc-start-date'],
				'start-time' => $_POST['mbwpc-start-time'],
				'end-date' => $_POST['mbwpc-end-date'],
				'end-time' => $_POST['mbwpc-end-time'],
				'location' => $_POST['mbwpc-location'],
				'enable-google-maps' => $_POST['mbwpc-enable-google-maps'],
				'priority' => $_POST['mbwpc-priority'],
			);
		}
		else
		{
			$now = current_time('timestamp');

			if ($post)
			{
				$all_day_event = get_post_meta($post->ID, 'mbwpc-all-day-event', true);
				$start = get_post_meta($post->ID, 'mbwpc-start', true);
				$end = get_post_meta($post->ID, 'mbwpc-end', true);

				$values = array(
					'calendar' => $post->post_parent,
					'all-day-event' => $all_day_event,
					'location' => get_post_meta($post->ID, 'mbwpc-location', true),
					'enable-google-maps' => get_post_meta($post->ID, 'mbwpc-enable-google-maps', true),
					'priority' => get_post_meta($post->ID, 'mbwpc-priority', true),
				);
			}
			else
			{
				$values = array(
					'calendar' => 0,
					'all-day-event' => $all_day_event = false,
					'location' => '',
					'enable-google-maps' => false,
					'priority' => 0,
				);

			}

			if (!isset($start) || !$start)
			{
				$start = strtotime(date(self::DATE_FORMAT . ' g:00 A' , $now));
				$start = strtotime('next hour', $start);
			}

			if (!isset($end) || !$end)
				$end = strtotime('next hour', $start);

			list($values['start-date'], $values['start-time']) = explode(',', date(self::DATE_FORMAT . ',' . self::TIME_FORMAT, $start) );
			list($values['end-date'], $values['end-time']) = explode(',', date(self::DATE_FORMAT . ',' . self::TIME_FORMAT, $end) );
		}

		// Require the view file.
    require(self::$plugin_path . 'admin-views/event-meta-box.php');
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
		// Do not proceed if it is not the correct post type.
		if ( $post->post_type != self::EVENT_POST_TYPE || defined('DOING_AJAX') )
			return;

		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') )
			return;

		// Check the nonce.
		if( !isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce($_POST[self::NONCE_NAME], self::EVENT_POST_TYPE) )
			return;

		// The user does not have the required access.
		if ( !current_user_can('publish_posts') )
			return;


		global $wpdb;

		// Set the calendar for the event.
		$calendar = isset($_POST['mbwpc-calendar']) && isset(self::$calendar_options[$_POST['mbwpc-calendar']]) ? $_POST['mbwpc-calendar'] : 0;
		$wpdb->update( $wpdb->posts, array('post_parent' => $calendar), array('ID' => $post_id) );


		// Delete all the meta for this post that was added by the plugin.
		$wpdb->query( $wpdb->prepare("DELETE FROM `$wpdb->postmeta` WHERE `post_id` = %d AND `meta_key` LIKE 'mbwpc-%%'", $post_id) );


		// Get the current time.
		$now = current_time('timestamp');

		// Set some values. We might need them a bit later.
		$start = strtotime(date(self::DATE_FORMAT . ' g:00 A' , $now));
		$start = strtotime('next hour', $start);
		$end = strtotime('next hour', $start);

		// Validate the start and end date.
		$valid_start_date = isset($_POST['mbwpc-start-date']) && self::validate_date($_POST['mbwpc-start-date']);
		$valid_end_date = isset($_POST['mbwpc-end-date']) && self::validate_date($_POST['mbwpc-end-date']);

		// The posted start date was valid, so we can use that.
		if ($valid_start_date)
			$start_date = $_POST['mbwpc-start-date'];

		// The posted end date was valid, so we can use that.
		if ($valid_end_date)
			$end_date = $_POST['mbwpc-end-date'];

		// Process the start and end date
		if ($valid_start_date && !$valid_end_date)
		{
			$end_date = $start_date;
		}
		else if (!$valid_start_date && $valid_end_date)
		{
			$start_date = $end_date;
		}
		else if (!$valid_start_date && !$valid_end_date)
		{
			$start_date = date(self::DATE_FORMAT, $start);
			$end_date = date(self::DATE_FORMAT, $end);
		}

		if ($start_date > $end_date)
			$start_date = $end_date;

		// Validate the start and end time.
		$valid_start_time = isset($_POST['mbwpc-start-time']) && self::validate_time($_POST['mbwpc-start-time']);
		$valid_end_time = isset($_POST['mbwpc-end-time']) && self::validate_time($_POST['mbwpc-end-time']);

		// The posted start time was valid, so we can use that.
		if ($valid_start_time)
			$start_time = $_POST['mbwpc-start-time'];

		// The posted end time was valid, so we can use that.
		if ($valid_end_time)
			$end_time = $_POST['mbwpc-end-time'];

		// Process the start and end time
		if ($valid_start_time && !$valid_end_time)
		{
			$end_time = $start_time;
		}
		else if (!$valid_start_time && $valid_end_time)
		{
			$start_time = $end_time;
		}
		else if (!$valid_start_time && !$valid_end_time)
		{
			$start_time = date(self::TIME_FORMAT, $start);
			$end_time = date(self::TIME_FORMAT, $end);
		}

		// If the start date is equal to the end date and the start time is higher than the end time, then make the start time the same as the end time.
		if ( strtotime($start_date . ' ' . $start_time) > strtotime($end_date . ' ' . $end_time) )
			$start_time = $end_time;

		// Add the values for the start and end time.
		add_post_meta( $post_id, 'mbwpc-start', strtotime($start_date . ' ' . $start_time) );
		add_post_meta( $post_id, 'mbwpc-end', strtotime($end_date . ' ' . $end_time) );


		// Add the value for "all day event".
		if ( isset($_POST['mbwpc-all-day-event']) )
			add_post_meta($post_id, 'mbwpc-all-day-event', 1);

		// Process the location.
		if ( isset($_POST['mbwpc-location']) )
			if ( $location = trim(strip_tags($_POST['mbwpc-location'])) )
				add_post_meta($post_id, 'mbwpc-location', $location);

		// Process the priority.
		if ( isset($_POST['mbwpc-priority']) && isset(self::$priority_options[$_POST['mbwpc-priority']]) && $_POST['mbwpc-priority'] > 0 )
			add_post_meta($post_id, 'mbwpc-priority', $_POST['mbwpc-priority']);
	}

	/**
   * Set the column headers.
   *
   * @param array $columns
   * @access public
   * @static
   */
	public static function column_headers($columns)
	{
		unset($columns['date']);

		$columns['calendar'] = 'Calendar';
		$columns['start-time'] = 'Start Time';
		$columns['end-time'] = 'End Time';

		return $columns;
	}

	/**
   * Set the sortable columns.
   *
   * @param array $columns
   * @access public
   * @static
   * @return array
   */
	public static function sortable_columns($columns)
	{
		$columns['calendar'] = 'calendar';
		$columns['start-time'] = 'start-time';
		$columns['end-time'] = 'end-time';

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
		if ( get_query_var('post_type') != self::EVENT_POST_TYPE )
			return;

		if ($column_id == 'calendar')
		{
			$calendar_id = self::$events[$post_id]->post_parent;

			if ( isset(self::$calendar_options[self::$events[$post_id]->post_parent]) )
			{
				$calendar = self::$calendar_options[self::$events[$post_id]->post_parent];
				echo '<a href="edit.php?post_type=' . self::EVENT_POST_TYPE . '&calendar=' . $calendar_id . '">' . $calendar . '</a>';
			}
		}

		if ($column_id == 'start-time')
		{
			if (self::$events[$post_id]->all_day_event)
				echo date(self::DATE_FORMAT, self::$events[$post_id]->start_time) . ' (All day event)';
			else
				echo date(self::DATE_FORMAT . ' ' . self::TIME_FORMAT, self::$events[$post_id]->start_time);
		}

		if ($column_id == 'end-time')
		{
			if (self::$events[$post_id]->all_day_event)
				echo date(self::DATE_FORMAT, self::$events[$post_id]->end_time) . ' (All day event)';
			else
				echo date(self::DATE_FORMAT . ' ' . self::TIME_FORMAT, self::$events[$post_id]->end_time);
		}
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
		if ( get_query_var('post_type') == self::EVENT_POST_TYPE )
			if ( is_null(self::$events) )
				foreach ($posts as $post)
					self::$events[$post->ID] = $post;

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
		if ( get_query_var('post_type') == self::EVENT_POST_TYPE )
			unset($actions['view']);

		return $actions;
	}

	/**
   * Process the tag row actions.
   *
   * @param array $actions
   * @access public
   * @static
   * @return array
   */
	public static function tag_row_actions($actions)
	{
		$current_screen = get_current_screen();

		if ($current_screen->post_type == self::EVENT_POST_TYPE)
			unset($actions['view']);

		return $actions;
	}

	/**
   * Add DISTINCT to the posts select query.
   *
   * @access public
   * @static
   * @return string
   */
	public static function posts_distinct()
	{
		if ( get_query_var('post_type') == self::EVENT_POST_TYPE )
			return 'DISTINCT';
	}

	/**
   * Add more fields to the posts select query.
   *
   * @param string $fields
   * @access public
   * @static
   * @return string
   */
	public static function posts_fields($fields)
	{
		if ( get_query_var('post_type') != self::EVENT_POST_TYPE )
			return $fields;

		global $wpdb;

		$fields .= ", `mbwpc_all_day_event`.`meta_value` AS `all_day_event`, `mbwpc_start`.`meta_value` AS `start_time`, `mbwpc_end`.`meta_value` AS `end_time`";

		return $fields;
	}

	/**
   * Add more conditions to the posts select query.
   *
   * @param string $where
   * @access public
   * @static
   * @return string
   */
	public function posts_where($where)
	{
		if ( get_query_var('post_type') != self::EVENT_POST_TYPE )
			return $where;

		global $wpdb;

		if ( isset($_GET['calendar']) && $_GET['calendar'] != '' && isset(self::$calendar_options[$_GET['calendar']]) )
			$where .= " AND `$wpdb->posts`.`post_parent` = " . $_GET['calendar'];

		return $where;
	}

	/**
   * Add a few joins to the posts select query.
   *
   * @param string $join
   * @access public
   * @static
   * @return string
   */
	public static function posts_join($join)
	{
		if ( get_query_var('post_type') != self::EVENT_POST_TYPE )
			return $join;

		global $wpdb;

		$join .= " LEFT JOIN `$wpdb->postmeta` AS `mbwpc_all_day_event` ON `$wpdb->posts`.`ID` = `mbwpc_all_day_event`.`post_id` AND `mbwpc_all_day_event`.`meta_key` = 'mbwpc-all-day-event' ";
		$join .= " LEFT JOIN `$wpdb->postmeta` AS `mbwpc_start` ON `$wpdb->posts`.`ID` = `mbwpc_start`.`post_id` AND `mbwpc_start`.`meta_key` = 'mbwpc-start' ";
		$join .= " LEFT JOIN `$wpdb->postmeta` AS `mbwpc_end` ON `$wpdb->posts`.`ID` = `mbwpc_end`.`post_id` AND `mbwpc_end`.`meta_key` = 'mbwpc-end' ";

		return $join;
	}

	/**
   * Add and order criteria to the posts select query.
   *
   * @param string $where
   * @access public
   * @static
   * @return string
   */
	public static function posts_orderby($orderby_sql)
	{
		if ( get_query_var('post_type') != self::EVENT_POST_TYPE )
			return $orderby_sql;

		$order = get_query_var('order') ? get_query_var('order') : 'ASC';
		$orderby = get_query_var('orderby') ? get_query_var('orderby') : 'start-time';

		if ($orderby == 'start-time')
			$orderby_sql = "`mbwpc_start`.`meta_value` $order";
		else if ($orderby == 'end-time')
			$orderby_sql = "`mbwpc_end`.`meta_value` $order";

		return $orderby_sql;
	}

	/**
   * Add a new filter to the admin event list.
   *
   * @access public
   * @static
   */
	public static function restrict_manage_posts()
	{
		if ( get_query_var('post_type') != self::EVENT_POST_TYPE )
			return;

		echo '<select id="mbwpc-calendar-filter" name="calendar">';

		echo '<option value="">Show all calendars</option>';

		foreach (self::$calendar_options as $id => $calendar)
			echo '<option value="'  . $id . '"' . ( isset($_GET['calendar']) && (int)$_GET['calendar'] === $id ? ' selected="selected"' : '' ) . '>'  . $calendar . '</option>';

		echo '</select> ';
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

		if ($current_screen->post_type == self::EVENT_POST_TYPE)
			foreach ($messages['post'] as $key => $value)
				$messages['post'][$key] = substr( $value, 0, strpos($value, '.') + 1 );

		return $messages;
	}
}

MBWPC_Admin_Event::get_instance();

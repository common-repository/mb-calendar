<?php

/**
 * MB Calendar - a versatile and powerful calendar/event system.
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
 * The calendar generator class.
 *
 * @package MB_Calendar
 */
class MB_Calendar
{
  /**
   * The days of the week.
   *
   * @var array Monday - Sunday.
   * @access public
   * @static
   */
  public static $days_of_the_week = array(
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday',
  );

  /**
   * The months.
   *
   * @var array January - December.
   * @access public
   * @static
   */
  public static $months = array(
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
  );

  /**
   * The event priorities.
   *
   * @var array
   * @access public
   * @static
   */
  public static $priorities = array(
    0 => array(
      'text' => 'Not specified',
      'class' => '',
    ),
    1 => array(
      'text' => 'Low',
      'class' => 'mbc-priority-low',
    ),
    2 => array(
      'text' => 'Normal',
      'class' => 'mbc-priority-normal',
    ),
    3 => array(
      'text' => 'High',
      'class' => 'mbc-priority-high',
    ),
  );

  /**
   * The "sort by" options.
   *
   * @var array
   * @access public
   * @static
   */
  public static $sort_by_options = array(
    0 => 'Date',
    1 => 'Priority',
    2 => 'Title'
  );

  /**
   * The "sort" options.
   *
   * @var array
   * @access public
   * @static
   */
  public static $sort_options = array(
    0 => 'Asc',
    1 => 'Desc',
  );

  /**
   * Did the init function execute?
   *
   * @var bool
   * @access private
   */
  private $init = false;

  /**
   * The current time.
   *
   * @var int UNIX timestamp.
   * @access protected
   */
  protected $current_time;

  /**
   * Current time -> day of the week.
   *
   * @var string Monday - Sunday.
   * @access protected
   */
  protected $current_day_of_the_week;

  /**
   * Selected time.
   *
   * @var int UNIX timestamp.
   * @access protected
   */
  protected $selected_time;

  /**
   * Selected time -> day of the week.
   *
   * @var string Monday - Sunday.
   * @access protected
   */
  protected $selected_day_of_the_week;

  /**
   * Selected time -> month.
   *
   * @var int 1 - 12.
   * @access protected
   */
  protected $selected_month;

  /**
   * Selected time -> month name.
   *
   * @var string January - December.
   * @access protected
   */
  protected $selected_month_name;

  /**
   * Selected time -> year.
   *
   * @var int
   * @access protected
   */
  protected $selected_year;

  /**
   * Selected time -> first day of the month.
   *
   * @var int UNIX timestamp.
   * @access protected
   */
  protected $selected_first_day;

  /**
   * Selected time -> last day of the month.
   *
   * @var int UNIX timestamp.
   * @access protected
   */
  protected $selected_last_day;

  /**
   * Selected time -> start time.
   *
   * @var int UNIX timestamp.
   * @access protected
   */
  protected $selected_start_time;

  /**
   * Selected time -> end time.
   *
   * @var int UNIX timestamp.
   * @access protected
   */
  protected $selected_end_time;

  /**
   * First day of the week.
   *
   * @var int 1 - 7.
   * @access protected
   */
  protected $selected_first_day_of_the_week = 1;

  /**
   * Display the day names from calendar header in the short form.
   *
   * E.g.: Mon.
   *
   * @var bool
   * @access protected
   */
  protected $calendar_header_short_day_names = true;

  /**
   * Add the month names to the 1st and last day of every month.
   *
   * E.g.: 30 Jun ...	1 Jul.
   *
   * @var bool
   * @access protected
   */
  protected $show_month_names = true;

  /**
   * Display the change view.
   *
   * @var bool
   * @access protected
   */
  protected $display_change_view = true;

  /**
   * Display the navigation.
   *
   * @var bool
   * @access protected
   */
  protected $display_navigation = true;

  /**
   * Display the sorting options.
   *
   * @var bool
   * @access protected
   */
  protected $display_sort = true;

  /**
   * The width of the calendar.
   *
   * @var int
   * @access protected
   */
  protected $width;

  /**
   * The view to display.
   *
   * @var string 'calendar', 'event_list'.
   * @access protected
   */
  protected $view = 'calendar';

  /**
   * The events array.
   *
   * @var array
   * @access protected
   */
  protected $events = array();

  /**
   * Sort by date, priority or title.
   *
   * @var string
   * @access protected
   */
  protected $sort_by = 'date';

  /**
   * Sort asc/desc.
   *
   * @var string
   * @access protected
   */
  protected $sort = 'asc';

  /**
   * Set the current time.
   *
   * @param int $current_time
   * @access public
   * @return MB_Calendar
   */
  public function set_current_time($current_time)
  {
    if ( is_numeric($current_time) )
      $this->current_time = $current_time;

    return $this;
  }

  /**
   * Set the selected time.
   *
   * @param int $selected_time
   * @access public
   * @return MB_Calendar
   */
  public function set_selected_time($selected_time)
  {
    if ( is_numeric($selected_time) )
      $this->selected_time = $selected_time;

    return $this;
  }

  /**
   * Set the first day of the week.
   *
   * @param int $first_day_of_the_week
   * @access public
   * @return MB_Calendar
   */
  public function set_first_day_of_the_week($first_day_of_the_week)
  {
    if ( in_array( $first_day_of_the_week, range(1, 7) ) )
      $this->selected_first_day_of_the_week = $first_day_of_the_week;

    return $this;
  }

  /**
   * Set the view.
   *
   * @param string $view 'calendar', 'event_list'.
   * @access public
   * @return MB_Calendar
   */
  public function set_view($view)
  {
    if ( in_array( $view, array('calendar', 'event_list') ) )
      $this->view = $view;

    return $this;
  }

  /**
   * Set $display_change_view.
   *
   * @param boolean $value
   * @access public
   * @return MB_Calendar
   */
  public function set_display_change_view($value)
  {
    $this->display_change_view = (bool)$value;

    return $this;
  }

  /**
   * Set $display_navigation.
   *
   * @param bool $value
   * @access public
   * @return MB_Calendar
   */
  public function set_display_navigation($value)
  {
    $this->display_navigation = (bool)$value;

    return $this;
  }

  /**
   * Set the width of the calendar.
   *
   * @param int $value
   * @access public
   * @return MB_Calendar
   */
  public function set_width($width)
  {
    if ( is_numeric($width) )
      $this->width = $width;

    return $this;
  }

  /**
   * Set the sort by option.
   *
   * @param string $sort_by
   * @access public
   * @return MB_Calendar
   */
  public function set_sort_by($sort_by)
  {
    if ( in_array( $sort_by, array('date', 'priority', 'title') ) )
      $this->sort_by = $sort_by;

    return $this;
  }

  /**
   * Set the sort option.
   *
   * @param string $sort
   * @access public
   * @return MB_Calendar
   */
  public function set_sort($sort)
  {
    if ( in_array( $sort, array('asc', 'desc') ) )
      $this->sort = $sort;

    return $this;
  }

  /**
   * Set $display_sort.
   *
   * @param boolean $value
   * @access public
   * @return MB_Calendar
   */
  public function set_display_sort($value)
  {
    $this->display_sort = (bool)$value;

    return $this;
  }

  /**
   * Add events.
   *
   * @param array $events
   * @access public
   * @return MB_Calendar
   */
  public function add_events($events)
  {
    if ( is_array($events) )
      $this->events = array_merge($this->events, $events);

    return $this;
  }

  /**
   * Return the months.
   *
   * @access public
   * @return array
   */
  public function get_months()
  {
    return self::$months;
  }

  /**
   * Initialize some variables.
   *
   * @access protected
   */
  protected function init()
  {
    // Stop the execution if we already executed the init function.
    if ($this->init)
      return;
    else
      $this->init = true;

    // Set the current time.
    if (!$this->current_time)
      // For non-WP scripts use: $this->current_time = time();
      $this->current_time = current_time('timestamp');

    // Current time -> current day of the week.
    $this->current_day_of_the_week = date('l', $this->current_time);

    // If the selected time is not set, then set it to the same value as the current time.
    if (!$this->selected_time)
      $this->selected_time = $this->current_time;

    // Selected time -> current day of the week.
    list($this->selected_day_of_the_week, $this->selected_month) = explode( ' ', date('l n', $this->selected_time) );

    // Selected time -> month and year.
    $date = date('F Y', $this->selected_time);
    list($this->selected_month_name, $this->selected_year) = explode(' ', $date);

    // Selected time -> first day of the month.
    $this->selected_first_day = strtotime('1 ' . $date);

    // Set the calendar start time.
    if ( date('l', $this->selected_first_day) != self::$days_of_the_week[$this->selected_first_day_of_the_week] )
      $this->selected_start_time = strtotime('last ' .  self::$days_of_the_week[$this->selected_first_day_of_the_week], $this->selected_first_day);
    else
      $this->selected_start_time = $this->selected_first_day;

    // Selected time -> last day of the week.
    $i = $this->selected_first_day_of_the_week + 6;
    $i = $i > 7 ? $i - 7 : $i;

    // Selected time -> last day of the month.
    $this->selected_last_day = strtotime( date('t ', $this->selected_first_day) . $date . ' 24:00' );

    // Set the calendar end time.
    if ( date('l', $this->selected_last_day) != self::$days_of_the_week[$i] )
      $this->selected_end_time = strtotime( 'next ' . self::$days_of_the_week[$i], $this->selected_last_day );
    else
      $this->selected_end_time = $this->selected_last_day;

    $this->selected_end_time = strtotime('tomorrow - 1 second', $this->selected_end_time);
  }

  /**
   * Generate the content.
   *
   * @access public
   * @return str
   */
  public function generate()
  {
    // Execute the init function.
    $this->init();

    // Set the width of the calendar and event list.
    $str = $this->width > 0 ? "<div style=\"width: {$this->width}px\">" : '';

    $str .= '<div class="mbc">';

    // Display the header.
    if ($this->display_change_view || $this->display_navigation)
    {
      $str .= '<div class="mbc-header">';

      // Display the change view button.
      if ($this->display_change_view)
        if ($this->view == 'calendar')
          $str .= '<div class="mbc-change-view mbc-display-event-list">Event List</div>';
        else
          $str .= '<div class="mbc-change-view mbc-display-calendar">Calendar</div>';

      $selected_title = date('F Y', $this->selected_time);

      // Display the month change controls if enabled.
      if ($this->display_navigation)
      {
        // Initialize some variables.
        $prev_title = date( 'F Y', strtotime('last month', $this->selected_time) );
        $next_title = date( 'F Y', strtotime('next month', $this->selected_time) );
        $current_title = date('F Y', $this->current_time);


        // Build the navigation.
        $str .= "<div class=\"mbc-change-month mbc-current-month\" title=\"Go to $current_title\">$current_title</div>";
        $str .= '<div class="mbc-navigation">';
        $str .= "<div class=\"mbc-change-month mbc-next-month\" title=\"$next_title\">&raquo;</div>";
        $str .= "<div class=\"mbc-change-month mbc-prev-month\" title=\"$prev_title\">&laquo;</div>";
        $str .= "<div class=\"mbc-selected-date\">$selected_title</div>";
        $str .= '</div><!-- .mbc-navigation -->';
      }
      else
      {
        // Display just the selected date.
        $str .= '<div class="mbc-navigation">';
        $str .= "<div class=\"mbc-selected-date\">$selected_title</div>";
        $str .= '</div><!-- .mbc-navigation -->';
      }

      $str .= '</div><!-- .mbc-header -->';
    }

    // Generate the proper view.
    $str .= $this->view == 'calendar' ? $this->generate_calendar() : $this->generate_event_list();

    // Add the loading "screen".
    $str .= '<div class="mbc-loading-cover mbc-hide"></div><div class="mbc-loading mbc-hide"></div>';

    $str .= '</div><!-- .mbc -->';

    $str .= $this->width > 0 ? '</div>' : '';

    return $str;
  }

  /**
   * Generate the calendar.
   *
   * @access public
   * @return str
   */
  public function generate_calendar()
  {
    // Execute the init function.
    $this->init();

    $str = '<table class="mbc-calendar-view" border="0" cellpadding="0" cellspacing="0">
              <tr>';

    $i = $this->selected_first_day_of_the_week;
    $j = 1;

    // Build the calendar header.
    while ($j <= 7)
    {
      // Show the current weekday only on the current month.
      $selected_day_of_the_week = false;

      if ($this->current_time >= $this->selected_start_time && $this->current_time <= $this->selected_end_time)
        $selected_day_of_the_week = $this->current_day_of_the_week == self::$days_of_the_week[$i];

      if ($this->calendar_header_short_day_names)
        $day_of_the_week = substr(self::$days_of_the_week[$i], 0, 3);

      $str .= '<th>' . ( $selected_day_of_the_week ? "<strong>$day_of_the_week</strong>" : $day_of_the_week ) . '</th>';

      $i = $i < 7 ? $i + 1 : 1;
      $j++;
    }

    $str .= '</tr>';

    // Set the start time.
    $time = $this->selected_start_time;

    // Used for adding the opening and closing table row tag.
    $i = 0;

    // Build the calendar.
    while ($time <= $this->selected_end_time)
    {

      // Add the opening table row tag when required.
      if ($i == 0 || $i % 7 == 0)
        $str .= '<tr>';

      // Set the day, number of days in the month, the day of the week and the month name for $time's current value.
      list($day, $days, $day_of_the_week, $month) = explode( ' ', date('j t l F', $time) );

      // Initialize some variables.
      $value = $day;
      $classes = array();

      // If enabled, add the month names to the 1st and last day of every month.
      if ( $this->show_month_names && ($day == 1 || $day == $days) )
        $value .= ' ' . substr($month, 0, 3);

      // Check if it's the weekend.
      $is_weekend = in_array( $day_of_the_week, array('Sunday', 'Saturday') );

      if ($month != $this->selected_month_name)
        // Check if the day belongs to another month than the current month.
        $classes[] = $is_weekend ? 'mbc-other-month-weekend-day' : 'mbc-other-month-day';
      else
        if ($is_weekend)
          $classes[] = 'mbc-weekend-day';

      // Check if the day has at least one event.
      if ( self::has_events($time, $this->events) )
      {
        $classes[] = 'mbc-has-events';
        $classes[] = 'mbc-date-' . date('Y-n-j', $time);
      }

      // Add the table cell.
      $str .= '<td' . ( $classes ? ' class="' . implode(' ', $classes) .  '"' : '' ) . '>' . $value . '</td>';

      // Add the closing table row tag when required.
      if ( ($i + 1) % 7 == 0 )
        $str .= '</tr>';

      // Increment the time variable by 24h.
      $time += 86400;

      ++$i;
    }

    $str .= '</table>';

    return $str;
  }

  /**
   * Check if the are any events at the given time.
   *
   * Return true at the 1st true condition,
   * because we only need to know if there is at least one event.
   *
   * @param int $time
   * @param mixed $events
   * @access protected
   * @static
   */
  protected static function has_events($time, $events)
  {
    // Check each event.
    foreach ($events as $event)
    {
      // Initialize some variables.
      $start = strtotime('midnight', $event['start']);
      $end = strtotime('tomorrow - 1 second', $event['end']);

      // Check if time is in the start - end period.
      if ($time >= $start && $time <= $end)
        return true;
    }

    return false;
  }

  /**
   * Generate the event list.
   *
   * @param mixed $start
   * @param mixed $end
   * @access protected
   * @return str
   */
  public function generate_event_list($start = null, $end = null)
  {
    // Execute the init function.
    $this->init();

    // If $start or $end are not defined, then set them to the UNIX time of the 1st and last day of the selected month.
    if (!$start || !$end)
    {
      $start = $this->selected_first_day;
      $end = $this->selected_last_day;
    }

    $str = '<div class="mbc-event-list-view">';

    $str .= '<div class="mbc-events">';

    $events = array();

    // Go through each event.
    foreach ($this->events as $event)
      // All good - add the event.
        if ($event['start'] >= $start && $event['start'] <= $end || $event['end'] >= $start && $event['end'] <= $end ||
            $start >= $event['start'] && $start <= $event['end'] || $end >= $event['start'] && $end <= $event['end']
            )
        $events[] = $event;

    // Proceed if there are any events to be displayed.
    if ($events)
    {
      // Display the sorting options if they are enabled and if we are displaying more than one event.
      if ( $this->display_sort && isset($events[1]) )
      {
        $str .= '<dl class="mbc-events-sort">';

        $sort = $this->sort == 'desc' ? 'asc' : 'desc';
        $sort_arrow = $this->sort == 'asc' ? '&uarr;' : '&darr;';

        $sort_by = array_reverse( self::$sort_by_options );

        foreach ($sort_by as $value)
        {
          $aux_value = strtolower($value);

          if ($this->sort_by == $aux_value)
            $str .= "<dd class=\"mbc-sort-by-$aux_value-$sort\">$value $sort_arrow</dd>";
          else
            $str .= "<dd class=\"mbc-sort-by-$aux_value\">$value</dd>";
        }

        $str .= '<dt>Sort by:</dt>';

        $str .= '</dl><!-- .mbc-events-sort -->';
      }

      // There is no need to sort by date because the the $events array is already ordered by date.

      // Sort by priority.
      if ($this->sort_by == 'priority')
      {
        function compare($a, $b)
        {
          if ($a['title'] == $b['title'])
            return 0;

          return ($a['title'] > $b['title']) ? -1 : 1;
        }

        usort($events, 'compare');

      // Sort by title.
      }
      else if ($this->sort_by == 'title')
      {
        function compare($a, $b)
        {
          if ($a['priority'] == $b['priority'])
            return 0;

          return ($a['priority'] > $b['priority']) ? -1 : 1;
        }

        usort($events, 'compare');
      }

      // Reverse the array if sorting desc.
      if ($this->sort == 'desc')
        $events = array_reverse($events);

      // Add the event html to the content string.
      foreach ($events as $event)
        $str .= $this->get_event_html($event);
    }
    else
    {
      $str .= 'No events..';
    }


    $str .= '</div><!-- .mbc-events -->';

    $str .= '</div><!-- .mbc-events-view -->';

    return $str;
  }

  /**
   * Build and return the html for the event.
   *
   * @param array $event An array containing the event data.
   * @access protected
   * @return str
   */
  protected function get_event_html($event)
  {
    // Init the string.
    $str = '<div class="mbc-event">';

    // Add the title.
    $str .= '<div class="mbc-event-title">' . $event['title'] . '</div>';

    // Add the priority.
    if ( isset(self::$priorities[$event['priority']]) )
      $str .= '<div title="Priority: ' . self::$priorities[$event['priority']]['text'] . '" class="mbc-event-priority' . (self::$priorities[$event['priority']]['class'] != '' ? ' ' . self::$priorities[$event['priority']]['class'] : '') . '">&nbsp;</div>';

    // Add the event info.
    $str .= '<div class="mbc-event-info">';

    // Process the time.
    if ( date('Y', $event['start']) != date('Y', $event['end']) )
      $date_format = ' j, Y';
    else
      $date_format = ' j';

    // Initialize some variables
    $start_date = date($date_format, $event['start']);
    $end_date = date($date_format, $event['end']);

    $start_month = date('F', $event['start']);
    $end_month = date('F', $event['end']);

    $start_time = date(' g:i A', $event['start']);
    $end_time =  date(' g:i A', $event['end']);

    // Add the time.
    if ($start_month . $start_date != $end_month . $end_date)
      if ($event['all_day_event'])
        $str .= $start_month . $start_date . ' - ' . $end_month . $end_date . ' (All day event)';
      else
        $str .= $start_month . $start_date . $start_time . ' - ' . $end_month . $end_date . $end_time;
    else
      if ($event['all_day_event'])
        $str .= $start_month . $start_date . ' (All day event)';
      else
        if ($start_time != $end_time)
          $str .= $start_month . $start_date . $start_time . ' - ' . $end_time;
        else
          $str .= $start_month . $start_date;

    // Add the location.
    if ($event['location'])
      $str .= ' at <strong><em>' . $event['location'] . '</em></strong>';

    $str .= '</div><!-- .mbc-event-info -->';

    // Add the processed event content.
    $str .= '<div class="mbc-event-desc mbc-hide">' . wpautop($event['description']) . '</div><!-- .mbc-event-desc -->';

    $str .= '</div>';

    return $str;
  }
}

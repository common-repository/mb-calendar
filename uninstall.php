<?php

/**
 * MB Calendar - a versatile and powerful calendar/event system for your WordPress website.
 *
 * @package MB_Calendar
 * @author Marian Bucur <thebigman@marianbucur.com>
 * @version 1.0
 * @copyright Marian Bucur
 */

// If uninstall not called from WordPress exit.
if ( !defined('WP_UNINSTALL_PLUGIN') )
  exit();

// :(

require('lib/mbwpc-base.class.php');

global $wpdb;

// Delete all the calendar/event posts and their meta.
$sql = "DELETE `$wpdb->posts`.*, `$wpdb->postmeta`.* FROM `$wpdb->posts`
        LEFT JOIN `$wpdb->postmeta` ON `post_id` = `ID`
        WHERE `post_type` = %s OR `post_type` = %s";

$wpdb->query( $wpdb->prepare($sql, MBWPC_Base::CALENDAR_POST_TYPE, MBWPC_Base::EVENT_POST_TYPE ) );

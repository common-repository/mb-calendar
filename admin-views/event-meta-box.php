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
?>

<?php wp_nonce_field(self::EVENT_POST_TYPE, self::NONCE_NAME); ?>

<table class="mbwpc-settings" border="0" cellspacing="0" cellpadding="0">

  <tr>
    <td width="150">Calendar</td>
    <td>
      <select name="mbwpc-calendar">
        <?php
        foreach (self::$calendar_options as $key => $option)
          echo '<option value="' . $key . '"' . ($values['calendar'] == $key ? ' selected="selected"' : '') . '>' . $option . '</option>';
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <hr class="mbwpc-separator" />
    </td>
  </tr>

  <tr>
    <td></td>
    <td>
      <label><input id="mbwpc-all-day-event" type="checkbox" name="mbwpc-all-day-event"<?php echo $values['all-day-event'] ? ' checked="checked"' : '';?> /> All day Event</label>
    </td>
  </tr>

  <tr>
    <td>Start</td>
    <td>
      <input id="mbwpc-start-date" type="text" name="mbwpc-start-date" value="<?php echo $values['start-date'];?>" /> <input id="mbwpc-start-time" type="text" name="mbwpc-start-time" value="<?php echo $values['start-time'];?>" />
    </td>
  </tr>

  <tr>
    <td>End</td>
    <td>
      <input id="mbwpc-end-date" type="text" name="mbwpc-end-date" value="<?php echo $values['end-date'];?>" /> <input id="mbwpc-end-time" type="text" name="mbwpc-end-time" value="<?php echo $values['end-time'];?>" />
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <hr class="mbwpc-separator" />
    </td>
  </tr>

  <tr>
    <td>Location</td>
    <td>
      <input type="text" name="mbwpc-location" value="<?php echo $values['location'];?>" /> <br />
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <hr class="mbwpc-separator" />
    </td>
  </tr>

  <tr>
    <td>Priority</td>
    <td>
      <select name="mbwpc-priority">
        <?php
        foreach (self::$priority_options as $key => $option)
          echo '<option value="' . $key . '"' . ($values['priority'] == $key ? ' selected="selected"' : '') . '>' . $option . '</option>';
        ?>
      </select>
    </td>
  </tr>

</table>

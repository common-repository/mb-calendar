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

<?php wp_nonce_field(self::CALENDAR_POST_TYPE, self::NONCE_NAME); ?>

<table class="mbwpc-settings" border="0" cellspacing="0" cellpadding="0">

  <tr>
    <td width="170">Default month:</td>
    <td>
      <label><input id="mbwpc-current-month" type="checkbox" name="mbwpc-current-month"<?php echo $values['current-month'] ? ' checked="checked"' : '';?> /> Set the current month as the default month</label>
    </td>
  </tr>

  <tr>
    <td></td>
    <td>
      Or select one:
      <select id="mbwpc-default-month" name="mbwpc-default-month">
        <?php
        foreach (MB_Calendar::$months as $key => $option)
          echo '<option value="' . $key . '"' . ($values['default-month'] == $key ? ' selected="selected"' : '') . '>' . $option . '</option>';
        ?>
      </select>
      <input id="mbwpc-default-year" type="text" name="mbwpc-default-year" value="<?php echo $values['default-year'];?>" />
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <hr class="mbwpc-separator" />
    </td>
  </tr>

  <tr>
    <td width="170">First day of the week:</td>
    <td>
      <select id="mbwpc-first-day-of-the-week" name="mbwpc-first-day-of-the-week">
        <?php
        foreach (MB_Calendar::$days_of_the_week as $key => $option)
          echo '<option value="' . $key . '"' . ($values['first-day-of-the-week'] == $key ? ' selected="selected"' : '') . '>' . $option . '</option>';
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
    <td>Default view:</td>
    <td>
      <select id="mbwpc-default-view" name="mbwpc-default-view">
        <?php
        foreach (self::$view_options as $key => $option)
          echo '<option value="' . $key . '"' . ($values['default-view'] == $key ? ' selected="selected"' : '') . '>' . $option . '</option>';
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td><label for="mbwpc-hide-view-change-button">Hide view change button:</label></td>
    <td>
      <input id="mbwpc-hide-view-change-button" type="checkbox" name="mbwpc-hide-view-change-button"<?php echo $values['hide-view-change-button'] ? ' checked="checked"' : '';?> />
    </td>
  </tr>

  <tr>
    <td><label for="mbwpc-hide-month-change-controls">Hide month change controls:</label></td>
    <td>
      <input id="mbwpc-hide-month-change-controls" type="checkbox" name="mbwpc-hide-month-change-controls"<?php echo $values['hide-month-change-controls'] ? ' checked="checked"' : '';?> />
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <hr class="mbwpc-separator" />
    </td>
  </tr>

  <tr>
    <td>Sort by:</td>
    <td>
      <select id="mbwpc-sort-by" name="mbwpc-sort-by">
        <?php
        foreach (MB_Calendar::$sort_by_options as $key => $option)
          echo '<option value="' . $key . '"' . ($values['sort-by'] == $key ? ' selected="selected"' : '') . '>' . $option . '</option>';
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td>Sort:</td>
    <td>
      <select id="mbwpc-sort" name="mbwpc-sort">
        <?php
        foreach (MB_Calendar::$sort_options as $key => $option)
          echo '<option value="' . $key . '"' . ($values['sort'] == $key ? ' selected="selected"' : '') . '>' . $option . '</option>';
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td><label for="mbwpc-hide-sort">Hide the sorting options:</label></td>
    <td>
      <input id="mbwpc-hide-sort" type="checkbox" name="mbwpc-hide-sort"<?php echo $values['hide-sort'] ? ' checked="checked"' : '';?> />
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <hr class="mbwpc-separator" />
    </td>
  </tr>

  <tr>
    <td>Width:</td>
    <td>
      <input id="mbwpc-width" type="text" name="mbwpc-width" value="<?php echo $values['width'];?>" /> (0 = auto)
    </td>
  </tr>

</table>

jQuery(document).ready(function($){

  function is_valid_date(year, month, day, hours, minutes, seconds, milliseconds)
  {
    if (year == null)
      return false;

    // Set some default values.
    // Note that the months are numbered from 0 to 11.
    month = month != null ? month - 1 : 0;
    // The days are numbered from 1 to 31.
    day = day != null ? day : 1;
    hours = hours != null ? hours : 0;
    minutes = minutes != null ? minutes : 0;
    seconds = seconds != null ? seconds : 0;
    milliseconds = milliseconds != null ? milliseconds : 0;

    // Create the Date Object.
    date = new Date(year, month, day, hours, minutes, seconds, milliseconds);

    // If any of these do not match, then the date is invalid.
    if ( year != date.getFullYear() || month != date.getMonth() || day != date.getDate() ||
         hours != date.getHours() || minutes != date.getMinutes() || seconds != date.getSeconds() ||
         milliseconds != date.getMilliseconds() )
      return false;

    return true;
  }

  function process_time(time)
  {
    // Trim the value
    time = $.trim(time);
    // Replace multiple spaces with only one space
    time = time.replace(/\s{2,}/g, ' ');
    // Replace multiple colons with only one colon
    time = time.replace(/:{2,}/g, ':');

    time_values_aux = time.split(' ');

    if (time_values_aux.length != 1 && time_values_aux.length != 2)
      return false;

    if (time_values_aux.length == 2) {
      time_values_aux[1] = time_values_aux[1].toUpperCase();

      if (time_values_aux[1] != 'AM' && time_values_aux[1] != 'PM')
        return false;
    }

    time_values = time_values_aux[0].split(':');

    if (time_values.length != 2)
      return false;

    // Remove leading zeros
    time_values[0] = parseInt(time_values[0], 10);
    time_values[1] = parseInt(time_values[1], 10);

    if (time_values[0] < 0 || time_values[0] > 23)
      return false;

    if (time_values[1] < 0 || time_values[1] > 59)
      return false;

    if (time_values[0] == 0)
    {
      hour = 12;
      meridiem = 'AM';
    }
    else if (time_values[0] < 12 && time_values_aux.length == 1)
    {
      hour = time_values[0];
      meridiem = 'AM';
    }
    else if (time_values[0] == 12 && time_values_aux.length == 1)
    {
      hour = 12;
      meridiem = 'PM';
    }
    else if (time_values[0] > 12)
    {
      hour = time_values[0] - 12;
      meridiem = 'PM';
    }
    else
    {
      hour = time_values[0];
      meridiem = time_values_aux[1];
    }

    minutes = time_values[1] < 10 ? '0' + time_values[1] : time_values[1];

    return hour + ':' + minutes + ' ' + meridiem;
  }

  // Remove the "Show all dates" select.
  $('[name="m"]').remove();

  // Hide the slug input from the "Event Categories" page.
  $('#tag-slug').parent().hide();

  // Hide the slug input from the "Edit Event Category" page.
  $('#edittag #slug').parents('tr').hide();


  if ($('#mbwpc-all-day-event').attr('checked') == 'checked') {
    $('#mbwpc-start-time').attr('disabled', 'disabled');
    $('#mbwpc-end-time').attr('disabled', 'disabled');
  }


  $('#mbwpc-all-day-event').click(function(){
    if ($(this).attr('checked') == 'checked')
    {
      $('#mbwpc-start-time').attr('disabled', 'disabled');
      $('#mbwpc-end-time').attr('disabled', 'disabled');
    }
    else
    {
      $('#mbwpc-start-time').attr('disabled', false);
      $('#mbwpc-end-time').attr('disabled', false);
    }
  });

  // Set the default values for all datepickers.
  if ( jQuery().datepicker )
    $.datepicker.setDefaults({
      dateFormat: 'm/d/yy',
    });


  // Set the initial values for the start and end date.
  var mbwpc_start_date = $('#mbwpc-start-date').val();
  var mbwpc_end_date = $('#mbwpc-end-date').val();


  // Set the initial values for the start and end time.
  var mbwpc_start_time = $('#mbwpc-start-time').val();
  var mbwpc_end_time = $('#mbwpc-end-time').val();


  // Create a datepicker for the start date.
  if ( jQuery().datepicker )
    $('#mbwpc-start-date').datepicker({
      onClose: function(dateText, inst){

        start_date_values = dateText.split('/');

        if (start_date_values.length != 3)
        {
          $(this).blur();
          alert('Invalid date!');
          $(this).val(mbwpc_start_date);
          return;
        }

        // Remove leading zeros.
        for (i = 0; i < start_date_values.length; i++)
          start_date_values[i] = parseInt(start_date_values[i], 10);

        if( !is_valid_date(start_date_values[2], start_date_values[0], start_date_values[1]) )
        {
          $(this).blur();
          alert('Invalid date!');
          $(this).val(mbwpc_start_date);
        }
        else
        {
          start_date = start_date_values.join('/');
          start_date_object = new Date(start_date);
          end_date_object = new Date(mbwpc_end_date);

          if (start_date_object > end_date_object)
          {
            $(this).blur();
            alert('The start date cannot be higher than the end date!');
            $(this).val(mbwpc_start_date);
          }
          else
          {

            start_time_object = new Date(start_date + ' ' + mbwpc_start_time);
            end_time_object = new Date(mbwpc_end_date + ' ' + mbwpc_end_time);

            if (start_time_object > end_time_object)
            {
              $(this).blur();
              alert('The start time cannot be higher than the end time!');
              $(this).val(mbwpc_start_date);
            }
            else
            {
              mbwpc_start_date = start_date;
              $(this).val(start_date);
              $('#mbwpc-end-date').datepicker( 'option', {minDate: new Date(dateText)} );
            }
          }
        }
      },
      maxDate: new Date(mbwpc_end_date),
    });


  // Create a datepicker for the end date.
  if ( jQuery().datepicker )
    $('#mbwpc-end-date').datepicker({
      onClose: function(dateText, inst){

        end_date_values = dateText.split('/');

        if (end_date_values.length != 3)
        {
          $(this).blur();
          alert('Invalid date!');
          $(this).val(mbwpc_end_date);
          return;
        }

        // Remove leading zeros.
        for (i = 0; i < end_date_values.length; i++)
          end_date_values[i] = parseInt(end_date_values[i]);

        if ( !is_valid_date(end_date_values[2], end_date_values[0], end_date_values[1]) )
        {
          $(this).blur();
          alert('Invalid date!');
          $(this).val(mbwpc_end_date);
        }
        else
        {
          start_date_object = new Date(mbwpc_start_date);
          end_date = end_date_values.join('/');
          end_date_object = new Date(end_date);

          if (start_date_object > end_date_object)
          {
            $(this).blur();
            alert('The end date cannot be lower than the start date!');
            $(this).val(mbwpc_end_date);
          }
          else
          {
            start_time_object = new Date(mbwpc_start_date + ' ' + mbwpc_start_time);
            end_time_object = new Date(end_date + ' ' + mbwpc_end_time);

            if (start_time_object > end_time_object)
            {
              $(this).blur();
              alert('The end time cannot be lower than the start time!');
              $(this).val(mbwpc_end_date);
            }
            else
            {
              mbwpc_end_date = end_date;
              $(this).val(end_date);
              $('#mbwpc-start-date').datepicker( 'option', {maxDate: new Date(dateText)} );
            }
          }
        }
      },
      minDate: new Date(mbwpc_start_date),
    });

  $('#mbwpc-start-time').blur(function(){

    start_time = process_time( $(this).val() );

    if (!start_time)
    {
      alert('Invalid time!');
      $(this).val(mbwpc_start_time);
    }
    else
    {
      start_date_object = new Date(mbwpc_start_date + ' ' + start_time);
      end_date_object = new Date(mbwpc_end_date + ' ' + mbwpc_end_time);

      if (start_date_object > end_date_object)
      {
        alert('The start time cannot be higher than the end time!');
        $(this).val(mbwpc_start_time);
      }
      else
      {
        mbwpc_start_time = start_time;
        $(this).val(start_time);
      }
    }
  });


  $('#mbwpc-end-time').blur(function(){

    end_time = process_time( $(this).val() );

    if (!end_time)
    {
      alert('Invalid time!');
      $(this).val(mbwpc_end_time);
    }
    else
    {
      start_date_object = new Date(mbwpc_start_date + ' ' + mbwpc_start_time);
      end_date_object = new Date(mbwpc_end_date + ' ' + end_time);

      if (start_date_object > end_date_object)
      {
        alert('The end time cannot be lower than the start time!');
        $(this).val(mbwpc_end_time);
      }
      else
      {
        mbwpc_end_time = end_time;
        $(this).val(end_time);
      }
    }
  });


  $('.mbwpc-shortcode').focus(function(){
    $(this).select();
  });


  if ( $('#mbwpc-current-month').attr('checked') )
    $('#mbwpc-default-month').parents('tr').addClass('mbwpc-hidden');

  $('#mbwpc-current-month').click(function(){
    if ( $('#mbwpc-current-month').attr('checked') )
      $('#mbwpc-default-month').parents('tr').addClass('mbwpc-hidden');
    else
      $('#mbwpc-default-month').parents('tr').removeClass('mbwpc-hidden');
  });


  if ( $('#mbwpc-default-view').val() == 0 )
    $('#mbwpc-hide-view-change-button').parents('tr').addClass('mbwpc-hidden');

  $('#mbwpc-default-view').change(function(){
    if ( $(this).val() == 0 )
      $('#mbwpc-hide-view-change-button').parents('tr').addClass('mbwpc-hidden');
    else
      $('#mbwpc-hide-view-change-button').parents('tr').removeClass('mbwpc-hidden');
  });


  $('.mbwpc-tabs li').click(function(){

    $('.mbwpc-tabs li').removeClass('mbwpc-active-tab');
    $(this).addClass('mbwpc-active-tab');

    $('.mbwpc-tab-content').addClass('mbwpc-hidden');

    index = $('.mbwpc-tabs li').index(this);

    $('.mbwpc-tab-content').eq(index).removeClass('mbwpc-hidden');
  });
});

jQuery(document).ready(function($){

  // Timer used for retrieving the content via AJAX when changing the month.
  var change_month_timeout;

  // The date of the last displayed content.
  var last_date;

  // Timer used for retrieving the content via AJAX when changing the view.
  var change_view_timeout;

  // The time of the selected day.
  var selected_date;

  function show_loading()
  {
    if ( $('.mbc-calendar-view').length > 0 && !$('.mbc-calendar-view').hasClass('mbc-hide') )
      height = $('.mbc-calendar-view').height();
    else if ( $('.mbc-event-list-view').length > 0 && !$('.mbc-event-list-view').hasClass('mbc-hide') )
      height = $('.mbc-event-list-view').height();

    $('.mbc-loading').height(height).removeClass('mbc-hide');
    $('.mbc-loading-cover').height(height).removeClass('mbc-hide');
  }

  function hide_loading()
  {
    $('.mbc-loading').addClass('mbc-hide');
    $('.mbc-loading-cover').addClass('mbc-hide');
  }

  $('.mbc-event-title').live('click', function(){

    description = $(this).parent().find('.mbc-event-desc');

    // Hide or show the content of the event.
    if (description.hasClass('mbc-hide'))
      description.removeClass('mbc-hide');
    else
      description.addClass('mbc-hide');
  });

  $('.mbc-change-month').click(function(){

    // Reset the timer.
    clearTimeout(change_month_timeout);

    // Init an aux variable.
    aux_selected_date = new Date( MBC.selected_date.getTime() );

    // Set the selected date.
    if ( $(this).hasClass('mbc-current-month') )
      MBC.selected_date = new Date( MBC.current_date.getTime() );
    else if ( $(this).hasClass('mbc-next-month') )
      MBC.selected_date.setMonth( MBC.selected_date.getMonth() + 1 );
    else if ( $(this).hasClass('mbc-prev-month') )
      MBC.selected_date.setMonth( MBC.selected_date.getMonth() - 1 );

    // Proceed only if the selected date is different than the current date.
    if ( selected_date || MBC.selected_date.getTime() != aux_selected_date.getTime() )
    {

      // Reset the selected date used for displaying events form a certain day.
      selected_date = null;

      // Change the text of the current selected month.
      $('.mbc-selected-date').html( MBC.months[MBC.selected_date.getMonth()] + ' ' + MBC.selected_date.getFullYear() );

      // Initialize prev and next.
      prev = new Date( MBC.selected_date.getTime() );
      next = new Date( MBC.selected_date.getTime() );

      // Decrement, respectively increment the selected date by one month.
      prev.setMonth( prev.getMonth() - 1 );
      next.setMonth( next.getMonth() + 1 );

      // Change the title for the prev and next month.
      $('.mbc-prev-month').attr( 'title', MBC.months[prev.getMonth()] + ' ' + prev.getFullYear() );
      $('.mbc-next-month').attr( 'title', MBC.months[next.getMonth()] + ' ' + next.getFullYear() );

      // Initialize the data object.
      data = {
        action: MBC.ajax_action,
        nonce: MBC.nonce,
        calendar_id: MBC.id,
        date: MBC.selected_date.getFullYear() + '-' + ( MBC.selected_date.getMonth() + 1 ) + '-' + MBC.selected_date.getDate()
      };

      // Decide whether to generate the calendar or event list.
      if ( $('.mbc-calendar-view').length > 0 && !$('.mbc-calendar-view').hasClass('mbc-hide') )
      {
        data.generate= 'calendar';
        var view_selector = '.mbc-calendar-view';
      }
      else
      {
        data.generate = 'event_list';
        var view_selector = '.mbc-event-list-view';
      }

      // Create a timer.
      change_month_timeout = setTimeout(function(){

        show_loading();

        // Perform the AJAX request.
        $.ajax(setttings = {
          url: MBC.ajax_url,
          data: data,
          dataType: 'html',
          type: 'POST',
          success: function(html){

            if (html)
              if ( $(view_selector).length > 0 )
                // Replace the view if it already exists.
                $(view_selector).replaceWith(html);
              else
                // Add the view to the calendar if it does not already exist.
                $('.mbc').append(html);

            hide_loading();
          }
        });
      }, MBC.ajax_delay);
    }
  });

  $('.mbc-change-view').click(function(){

    // Reset the timer.
    clearTimeout(change_view_timeout);

    if ( $(this).hasClass('mbc-display-event-list') )
    {
      // Change the text of the current selected date.
      if (selected_date)
        $('.mbc-selected-date').html(selected_date[2] + ' ' + MBC.months[selected_date[1] - 1] + ' ' + selected_date[0]);

      $(this).removeClass('mbc-display-event-list');
      $(this).addClass('mbc-display-calendar');
      // Change the text of the button.
      $(this).html(MBC.change_view['calendar']);

      // Set a few variables.
      var show = '.mbc-event-list-view';
      var hide = '.mbc-calendar-view';
      generate = 'event_list';

    }
    else
    {
      // Change the text of the current selected date.
      if (selected_date)
        $('.mbc-selected-date').html( MBC.months[MBC.selected_date.getMonth()] + ' ' + MBC.selected_date.getFullYear() );

      $(this).removeClass('mbc-display-calendar');
      $(this).addClass('mbc-display-event-list');
      // Change the text of the button.
      $(this).html(MBC.change_view['event_list']);

      // Set a few variables.
      var show = '.mbc-calendar-view';
      var hide = '.mbc-event-list-view';
      generate = 'calendar';
    }

    // Check whether or not to retrieve the content via AJAX.
    if ( $(show).length == 0 || typeof(last_date) != 'object' || ( typeof(last_date) == 'object' && last_date.getTime() != MBC.selected_date.getTime() ) )
    {
      show_loading();

      // Create a timer.
      change_view_timeout = setTimeout(function(){

        data = {
          action: MBC.ajax_action,
          nonce: MBC.nonce,
          calendar_id: MBC.id,
          date:  MBC.selected_date.getFullYear() + '-' + ( MBC.selected_date.getMonth() + 1) + '-' + MBC.selected_date.getDate(),
          generate: generate,
        }

        // Perform the AJAX request.
        $.ajax(setttings = {
          url: MBC.ajax_url,
          data: data,
          dataType: 'html',
          type: 'POST',
          success: function(html){

            $(hide).addClass('mbc-hide');

            if ( $(show).length > 0 )
              $(show).replaceWith(html);
            else
              $('.mbc').append(html);

            hide_loading();
          }
        });
      }, MBC.ajax_delay);

    }
    else
    {
      hide_loading();
      $(hide).addClass('mbc-hide');
      $(show).removeClass('mbc-hide');
    }

    last_date = new Date( MBC.selected_date.getTime() );
  });

  $('.mbc-has-events').live('click', function(){

    change_view_button = $('.mbc-change-view');

    change_view_button.removeClass('mbc-display-event-list');
    change_view_button.addClass('mbc-display-calendar');
    change_view_button.html(MBC.change_view['calendar']);

    show_loading();

    date = $(this).attr('class').match(/mbc-date-(\d+-\d+-\d+)/)[1];
    selected_date = date.split('-');

    // Change the text of the current selected date.
    $('.mbc-selected-date').html(selected_date[2] + ' ' + MBC.months[selected_date[1] - 1] + ' ' + selected_date[0]);

    data = {
      action: MBC.ajax_action,
      nonce: MBC.nonce,
      calendar_id: MBC.id,
      date: date,
      generate: 'day_event_list',
    }

    // Perform the AJAX request.
    $.ajax(setttings = {
      url: MBC.ajax_url,
      data: data,
      dataType: 'html',
      type: 'POST',
      success: function(html){

        show = '.mbc-event-list-view';

        $('.mbc-calendar-view').addClass('mbc-hide');

        if ( $(show).length > 0 )
          $(show).replaceWith(html);
        else
          $('.mbc').append(html);

        hide_loading();
      }
    });
  });


  $('.mbc-events-sort dd').live('click', function(){

    // Create a timer.
    show_loading();

    data = {
      action: MBC.ajax_action,
      nonce: MBC.nonce,
      calendar_id: MBC.id,
      sort: $(this).attr('class').match(/mbc-sort-by-(.*)/)[1],
    }

    if (selected_date)
    {
      data['generate'] = 'day_event_list';
      data['date'] = selected_date.join('-');
    }
    else
    {
      data['generate'] = 'event_list';
      data['date'] = MBC.selected_date.getFullYear() + '-' + ( MBC.selected_date.getMonth() + 1) + '-' + MBC.selected_date.getDate();
    }

    // Perform the AJAX request.
    $.ajax({
      url: MBC.ajax_url,
      data: data,
      dataType: 'html',
      type: 'POST',
      success: function(html){
        $('.mbc-event-list-view').replaceWith(html);
        hide_loading();
      }
    });
  })
});

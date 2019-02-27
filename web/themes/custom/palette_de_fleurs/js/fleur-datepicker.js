/**
 * @file
 * Datepicker styling JS.
 */

(function ($, Drupal) {
  Drupal.behaviors.fleurDatepicker = {
    attach: function (context, settings) {
      $('input[type=date]', context).once('fleur-datepicker').each(function () {
        var $dateInput = $(this);
        var $textInput = $('<input/>')
          .attr('type', 'text')
          .attr('placeholder', Drupal.t('Select date'))
          .addClass('form-control');

        var $inputWrapper = $('<div />')
          .addClass('form-group has-feedback')
          .append($textInput);

        $inputWrapper.insertAfter($dateInput);
        $textInput.val($dateInput.val());

        var $icon = $('<i class="datepicker-icon glyphicon fleur-icon-16-calendar-primary form-control-feedback"></i>');
        $icon.insertAfter($textInput);

        var defaultDate = $dateInput.val() ? new Date($dateInput.val()) : undefined;
        var currentDate = new Date();
        currentDate.setHours(0,0,0,0);
        $textInput.datetimepicker({
          format: 'L',
          // Current date.
          minDate: currentDate,
          showClose: true,
          date: defaultDate,
          icons: {
            up: 'fleur-font-icon-16-chevron-up-primary',
            down: 'fleur-font-icon-16-chevron-down-primary',
            previous: 'fleur-font-icon-16-chevron-left-primary',
            next: 'fleur-font-icon-16-chevron-right-primary',
            close: 'fleur-font-icon-16-close-primary',
          }
        });
        $textInput.on('dp.change', function () {
          $dateInput.val($textInput.data("DateTimePicker").viewDate().format('YYYY-MM-DD'));
        });
      });

    }
  }
})(jQuery, Drupal);

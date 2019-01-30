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

        var $icon = $('<i class="datepicker-icon glyphicon glyphicon-calendar form-control-feedback"></i>');
        $icon.insertAfter($textInput);

        var defaultDate = $dateInput.val() ? new Date($dateInput.val()) : undefined;
        $textInput.datetimepicker({
          format: 'L',
          // Current date.
          minDate: new Date(),
          showClose: true,
          date: defaultDate,
        });
        $textInput.on('dp.change', function () {
          $dateInput.val($textInput.data("DateTimePicker").viewDate().format('YYYY-MM-DD'));
        });
      });

    }
  }
})(jQuery, Drupal);

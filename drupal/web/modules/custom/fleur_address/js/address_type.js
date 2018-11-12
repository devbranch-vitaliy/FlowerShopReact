/**
 * Change cart flyout function
 * @preserve
 */

(function ($, Drupal, debounce) {
  Drupal.behaviors.address_type = {
    attach: function attach(context, settings) {

        var compoane_field = $('.form-item-address-0-address-organization');

        function companyVisible(){
            if ($('#edit-field-address-type-0[checked="checked"]').length && !compoane_field.hasClass('hide')) compoane_field.addClass('hide');
            if ($('#edit-field-address-type-1[checked="checked"]').length && compoane_field.hasClass('hide')) compoane_field.removeClass('hide');
        }

        $('#edit-field-address-type').once().each(function() {
            companyVisible();
        });

        $('#edit-field-address-type-0').once().on('click', function () {
            if (!compoane_field.hasClass('hide')) compoane_field.addClass('hide');
            let company_value = $('#edit-address-0-address-organization');
            if (company_value.length) company_value[0].value = "";
        });

        $('#edit-field-address-type-1').once().on('click', function () {
            if (compoane_field.hasClass('hide')) compoane_field.removeClass('hide');
        });

    }
  };
})(jQuery, Drupal, Drupal.debounce);

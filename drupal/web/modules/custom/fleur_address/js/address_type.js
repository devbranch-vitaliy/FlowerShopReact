/**
 * Change cart flyout function
 * @preserve
 */

(function ($, Drupal, debounce) {
  Drupal.behaviors.address_type = {
    attach: function attach(context, settings) {

        var idList = {
            'profile': {
                wrapper         : '#edit-field-address-type',
                company_field   : '.form-item-address-0-address-organization',
                company_value   : '#edit-address-0-address-organization',
            },
            'shipping': {
                wrapper         : '#edit-shipping-information-shipping-profile-field-address-type',
                company_field   : '.form-item-shipping-information-shipping-profile-address-0-address-organization',
                company_value   : '#edit-shipping-information-shipping-profile-address-0-address-organization',
            },
            'payment': {
                wrapper         : '#edit-payment-information-add-payment-method-billing-information-field-address-type',
                company_field   : '.form-item-payment-information-add-payment-method-billing-information-address-0-address-organization',
                company_value   : '#edit-payment-information-add-payment-method-billing-information-address-0-address-organization',
            },
        };

        function companyVisible(obj){
            let compoane_field = $(obj.company_field);

            // Home option
            if ($(obj.wrapper+'-0[checked="checked"]').length && !compoane_field.hasClass('hide')) compoane_field.addClass('hide');

            // Company option
            if ($(obj.wrapper+'-1[checked="checked"]').length && compoane_field.hasClass('hide')) compoane_field.removeClass('hide');
        }

        Object.values(idList).forEach(function (value) {
            let compoane_field = $(value.company_field);

            $(value.wrapper).once().each(function() {
                companyVisible(value);
            });

            // Home click
            $(value.wrapper+'-0').once().on('click', function () {
                if (!compoane_field.hasClass('hide')) compoane_field.addClass('hide');
                let company_value = $(value.company_value);
                if (company_value.length) company_value[0].value = "";
            });

            // Company click
            $(value.wrapper+'-1').once().on('click', function () {
                if (compoane_field.hasClass('hide')) compoane_field.removeClass('hide');
            });
        })

    }
  };
})(jQuery, Drupal, Drupal.debounce);

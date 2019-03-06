/**
 * Show/hide Company field based on the Address Type field value.
 */

(function ($, Drupal) {
  Drupal.behaviors.fleur_address_type = {
    attach: function attach(context, settings) {
      $('.field--name-field-address-type input[type=radio]', context).each(function (e) {
        var $this = $(this);
        var showHideCompanyField = function ($input) {
          if (!$input.is(':checked')) {
            return;
          }
          var $field_wrapper = $this.closest('.field--name-field-address-type');
          var $address_wrapper = $field_wrapper.parent().find('.field--type-address');
          var $company_input = $address_wrapper.find('input.organization').closest('.form-item');
          var $company_hiden = $address_wrapper.find('.organization-hidden-field');

          if ($company_hiden.length == 0) {
            $company_hiden = $('<div></div>')
                .addClass('organization-hidden-field')
                .addClass('hidden-xs')
                .addClass('form-group')
                .addClass('col-sm-6')
                .insertAfter($company_input);
          }

          if ($this.val() == 1) {
            $company_input.show();
            $company_hiden.show();
          }
          else {
            $company_input.hide();
            $company_hiden.hide();
          }
        };
        showHideCompanyField($this);
        $this.once('address-type').change(function() {showHideCompanyField($this)});
      });
    }
  };
})(jQuery, Drupal);

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
          if ($this.val() == 1) {
            $company_input.show();
          }
          else {
            $company_input.hide();
          }
        };
        showHideCompanyField($this);
        $this.once('address-type').change(function() {showHideCompanyField($this)});
      });
    }
  };
})(jQuery, Drupal);

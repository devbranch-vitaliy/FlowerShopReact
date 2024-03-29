/**
 * @file
 * Operations with extras selection form.
 *
 * Show/hide Children elements.
 * Select image when radio/checkbox selected.
 */

(function ($, Drupal) {
  Drupal.behaviors.fleur_add_extras = {
    attach: function attach(context, settings) {

      $('input.extras_select_option', context).once().each(function (e) {
        var $this = $(this);

        var $variations_wrapper = $this.closest('.product-container');
        var $input_wrapper = $this.closest('label');
        var $image = $variations_wrapper.find(`div[data-variation-id=${$this.attr('data-variation-id')}].extras_img_wrapper`);
        $image.insertBefore($input_wrapper);

        var controlChildVariation = function ($input) {
          var $variation_wrapper = $input.closest('.variation_wrapper');
          var $children = $variation_wrapper.find('.extras_select_child_option');

          if (!$children.length) {
            return;
          }

          // Check if any element selected.
          var has_selected = false;
          $children.each(function () {
            if ($(this).is(':checked')) {
              has_selected = true;
              return true;
            }
          });

          if (has_selected) {
            $children.prop('checked', false);
          }

        };

        var elementChecked = function ($input) {

          var checked = $input.is(':checked');

          // For checkboxes.
          if ($input.attr('type') == "checkbox") {
            var $variation_wrapper = $this.closest('.variation_wrapper');
            if (checked) {
              $variation_wrapper.find('.children_variations').fadeIn();
            }
            else {
              $variation_wrapper.find('.children_variations').fadeOut();
              controlChildVariation($input);
            }
          }

          // Set image active.
          if (checked) {
            // If radios then uncheck all.
            if ($input.attr('type') == "radio") {
              $variations_wrapper.find('div.extras_img_wrapper').removeClass('active');
            }

            $image.addClass('active');
          }
          else {
            $image.removeClass('active');
          }
        };
        elementChecked($this);
        $this.once('selected-option').on('change', function () {elementChecked($this)});
      });
    }
  };
})(jQuery, Drupal);

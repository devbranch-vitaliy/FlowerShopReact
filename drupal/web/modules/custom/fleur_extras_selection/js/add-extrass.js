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

      $('input.extras_select_option', context).each(function (e) {
        let $this = $(this);

        let controlChildVariation = function ($input) {
          let $has_selected = false;
          let $variation_wrapper = $input.closest('.variation_wrapper');
          let children = $variation_wrapper.find('.extras_select_child_option');

          if (!children.length) {
            return;
          }
          // Check if any element selected.
          children.each(function (element) {
            if ($(this).is(':checked')) {
              $has_selected = true;
              return true;
            }
          });

          if ($has_selected) {
            Array.from(children).reduce((memo, el) => el.checked = false)
          }

        };

        let imageActivate = function ($input) {

          let $checked = $input.is(':checked');
          let $variations_wrapper = $this.closest('.product-container');

          // If radios then uncheck all.
          if ($input[0].getAttribute('type') == "radio") {
            $variations_wrapper.find('div.extras_img_wrapper').removeClass('active');
          }

          // For checkboxes.
          if ($input[0].getAttribute('type') == "checkbox") {
            let $variation_wrapper = $this.closest('.variation_wrapper');
            if ($checked) {
              $variation_wrapper.find('.children_variations').fadeIn();
            }
            else {
              $variation_wrapper.find('.children_variations').fadeOut();
              controlChildVariation($input);
            }
          }

          // Set image active.
          let $image = $variations_wrapper.find(`div[data-variation-id=${$input[0].getAttribute('data-variation-id')}].extras_img_wrapper`);

          if ($checked) {
            $image.addClass('active');
          }
          else {
            $image.removeClass('active');
          }
        };
        imageActivate($this);
        $this.once('selected-option').on('change', function () {imageActivate($this)});
      });
    }
  };
})(jQuery, Drupal);

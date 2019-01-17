/**
 * @file
 * JS code for mobile filters on the products page.
 */

(function ($, Drupal, window) {
    Drupal.behaviors.fleurMobileFilter = {
        attach: function (context, settings) {
            $('.views-exposed-form', context)
                .filter(function () {
                    return !!$(this).parents('.view-flowers').length;
                })
                .once()
                .each(function () {
                    var $form = $(this);
                    var $container = $('<div class="mobile-filter-container"></div>');
                    $form.find('select').each(function () {
                        var $select = $(this);
                        $select.find('option').each(function () {
                            var $option = $(this);
                            var optionVal = $option.val();
                            if (optionVal.toLowerCase() === 'all') {
                                $('<div></div>')
                                    .text($option.text())
                                    .appendTo($container);
                                var select_text = 'All';
                            }
                            else {
                                var select_text = $option.text();
                            }

                            var $link = $('<a></a>')
                                .attr('href', window.location.pathname + '?' + $.param({[$select.attr('name')]: optionVal}))
                                .text(select_text);
                            $link.click(function (e) {
                                e.preventDefault();
                                $form.find('select option').prop('selected', false);
                                $option.prop('selected', true);
                                $option.change();
                            });
                            $link.appendTo($container);

                        });
                    });
                    $container.appendTo($form);
                })
        }
    }
})(jQuery, Drupal, window);

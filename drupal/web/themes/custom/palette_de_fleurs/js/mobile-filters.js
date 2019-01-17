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
                    var $filter_container_lvl1 = $('<ul class="filter-head label-small inner"></ul>');

                    $('<span class="filter-toggle filter-name-head">Filter</span>').appendTo($filter_container_lvl1);

                    var $filter_container_lvl2 = $('<ul class="filter label-small inner"></ul>');

                    $form.find('select').each(function () {
                        var $filter_menu = $('<li></li>');
                        var $filter_options = $('<ul class="filter-option inner"></ul>');

                        var $select = $(this);
                        $select.find('option').each(function () {
                            var $option = $(this);
                            var optionVal = $option.val();
                            if (optionVal.toLowerCase() === 'all') {
                                $('<span class="filter-toggle filter-name"></span>')
                                    .text($option.text())
                                    .appendTo($filter_menu);
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
                            $link.appendTo($('<li></li>').appendTo($filter_options));
                        });
                        $filter_options.appendTo($filter_menu);
                        $filter_menu.appendTo($filter_container_lvl2);
                    });
                    $filter_container_lvl2.appendTo($filter_container_lvl1);
                    $filter_container_lvl1.appendTo($container);
                    $('.view-filters').after($container);

                    $('.filter-toggle', $container).once().each(function () {
                        var $this = $(this);

                        if ($this.next().hasClass('show')) {
                            $this.addClass('fleur-font-icon-16-chevron-up-primary').removeClass('fleur-font-icon-16-chevron-down-primary');
                        }
                        else {
                            $this.removeClass('fleur-font-icon-16-chevron-up-primary').addClass('fleur-font-icon-16-chevron-down-primary');
                        }
                    });

                    $('.filter-toggle', $container).click(function (e) {
                        e.preventDefault();

                        var $this = $(this);

                        if ($this.next().hasClass('show')) {
                            $this.removeClass('fleur-font-icon-16-chevron-up-primary');
                            $this.next().slideUp(350);
                            $this.next().removeClass('show');
                        }
                        else {
                            $this.parent().parent().find('.filter-toggle').removeClass('fleur-font-icon-16-chevron-up-primary');
                            $this.parent().parent().find('li .inner').slideUp(350);
                            $this.parent().parent().find('li .inner').removeClass('show');
                            $this.next().slideToggle(350);
                            $this.next().toggleClass('show');
                            $this.addClass('fleur-font-icon-16-chevron-up-primary');
                        }
                    });
                });
        }
    }
})(jQuery, Drupal, window);

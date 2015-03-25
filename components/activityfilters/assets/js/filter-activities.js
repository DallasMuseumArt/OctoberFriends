(function($) {
    $('a.activity-filter-all').click(function() {
        list = $(this).parent().parent();

        // make sure all categories are flagged active when view all chosen
        list.find('a.inactive').removeClass('inactive').addClass('active');
    });

    $('a.activity-filter').click(function() {
        var filter = $(this);
        var list = filter.parent().parent();

        // Toggle active/inactive filter state for display
        if (filter.hasClass('active')) {
            filter.removeClass('active').addClass('inactive');
        }
        else {
            filter.removeClass('inactive').addClass('active');
        }

        var active_filters = 'all'; // default all categories are active
        // generate a comma-delimited list of active categories if not all categories are active
        if (list.find('.inactive').length != 0) {
            active_filters = list
                .find('.activity-filter.active')
                .map(function(i, e) {
                    return $(e).data('filter-name');
                })
                .get()
                .join();
        }

        // initialize options for AJAX request
        var options = {
            data: { filter: active_filters }
        };

        // If we're specifying partial and target element properties, use them
        if (list.data('filter-element') != '') {
            var update = {};
            if (list.data('filter-partial') != '') {
                update[list.data('filter-partial')] = list.data('filter-element');
            }
            else {
                update['@default'] = list.data('filter-element'); 
            }
            options['update'] = update;
        }

        // Send the AJAX request to update the page
        $.request(list.data('filter-component'), options);

        return false;
    });
})(jQuery);
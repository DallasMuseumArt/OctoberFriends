(function($) {
    $('a.friends-activity-filter-all').click(function() {
        var list = $(this).parent().parent();

        // make sure all categories are flagged active when view all chosen
        list.find('a.inactive').removeClass('inactive').addClass('active');
    });

    $('a.friends-activity-filter').click(function() {
        var filter = $(this);
        var list = filter.parent().parent();

        // Toggle active/inactive filter state for display
        if (filter.hasClass('active')) {
            filter.removeClass('active').addClass('inactive');
        }
        else {
            filter.removeClass('inactive').addClass('active');
        }

        var active_filters = {
            categories: 'all',
            search: ''
        }; 

        // generate a list of active categories if not all categories are active
        if (list.find('.friends-activity-filter.inactive').length != 0) {
            active_filters['categories'] = list
                .find('.friends-activity-filter.active')
                .map(function(i, e) {
                    return $(e).data('filter-name');
                })
                .get();
        }

        // initialize options for AJAX request
        var options = {
            data: { filters: JSON.stringify(active_filters) }
        };

        //console.log(options.data.filters);

/*
 * Something's not right with the way October's AJAX handlers deal with
 * the 'update' property. The ActivityCodeForm onSubmit handler is firing when
 * the activitylist partial/element is explicitly targeted, but not when no
 * specific target is specified.
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
*/

        // Send the AJAX request to update the page
        $.request(list.data('filter-component'), options);

        return false;
    });
})(jQuery);
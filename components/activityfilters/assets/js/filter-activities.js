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

        // Initialize filters array with default values
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

        // Send the AJAX request to update the page
        $.request(list.data('filter-component'), options);

        return false;
    });
})(jQuery);
(function($) {

    var ActivityFilters = {};

    ActivityFilters.setCookie = function(value) {
        var expires = new Date();
        expires.setHours(24,0,0,0);
        document.cookie = 'activityfilters=' + encodeURIComponent(value) + '; path=/; expires=' + expires.toUTCString();
    };

    ActivityFilters.getCookie = function() {
        var value = document.cookie.match('(^|;) ?activityfilters=([^;]*)(;|$)' );
        return value ? decodeURIComponent(value[2]) : null;
    };

    ActivityFilters.list = $('.friends-activity-filters-list');

    ActivityFilters.isAllSelected = function() {
        return this.list.find('.friends-activity-filter.inactive').length == 0;
    };

    ActivityFilters.sendUpdate = function() {
        // Initialize filters array with default values
        var active_filters = {
            categories: 'all',
            search: ''
        };

        // generate a list of active categories if not all categories are active
        if (!this.isAllSelected()) {
            active_filters.categories = this.list
                                            .find('.friends-activity-filter.active')
                                            .map(function(i, e) {
                                                return $(e).data('filter-name');
                                            })
                                            .get();
        }

        active_filters = JSON.stringify(active_filters);

        this.setCookie(active_filters);

        // initialize options for AJAX request
        var options = {
            data: { filters: active_filters }
        };

        // Send the AJAX request to update the page
        $.request(this.list.data('filter-component'), options);
    };

    ActivityFilters.updateSelectAll = function() {
        if (this.isAllSelected()) {
            $('a.friends-activity-filter-all').removeClass('select').addClass('deselect');
        }
        else {
            $('a.friends-activity-filter-all').removeClass('deselect').addClass('select');
        }
    };

    ActivityFilters.updateState = function() {
        var active_filters = JSON.parse(this.getCookie());

        if (!active_filters) return;

        if (active_filters.categories == 'all') return;

        if (active_filters.categories instanceof Array) {
            // update list state
            $('a.friends-activity-filter').each(function() {
                var link = $(this);
                if ($.inArray(link.data('filter-name'), active_filters.categories) > -1) {
                    link.removeClass('inactive').addClass('active');
                }
                else {
                    link.removeClass('active').addClass('inactive');
                }
            });
        }
    };

    ActivityFilters.listen = function() {
        this.updateState();
        this.updateSelectAll();

        $('a.friends-activity-filter-all').click(function() {
            if (ActivityFilters.isAllSelected()) {
                // Deselect all
                ActivityFilters.list.find('a.active').removeClass('active').addClass('inactive');
            }
            else {
                // make sure all categories are flagged active when view all chosen
                ActivityFilters.list.find('a.inactive').removeClass('inactive').addClass('active');
            }
            
            ActivityFilters.updateSelectAll();

            ActivityFilters.sendUpdate();
            return false;
        });

        $('a.friends-activity-filter').click(function() {
            var filter = $(this);

            // Toggle active/inactive filter state for display
            if (filter.hasClass('active')) {
                filter.removeClass('active').addClass('inactive');
            }
            else {
                filter.removeClass('inactive').addClass('active');
            }

            ActivityFilters.updateSelectAll();

            ActivityFilters.sendUpdate();
            return false;
        });
    };

    ActivityFilters.listen();

})(jQuery);
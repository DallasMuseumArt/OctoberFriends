var ActivityFilters = {};

(function($) {

    ActivityFilters.filters = {
        categories: 'all',
        search: '',
        sort: ''
    };

    ActivityFilters.list = $('.friends-activity-filters-list');

    ActivityFilters.setCookie = function(value) {
        var expires = new Date();
        expires.setHours(24,0,0,0);
        document.cookie = 'activityfilters=' + encodeURIComponent(value) + '; path=/; expires=' + expires.toUTCString();
    };

    ActivityFilters.getCookie = function() {
        var value = document.cookie.match('(^|;) ?activityfilters=([^;]*)(;|$)' );
        return value ? decodeURIComponent(value[2]) : null;
    };

    ActivityFilters.isAllSelected = function() {
        return this.list.find('.friends-activity-filter.inactive').length == 0;
    };

    ActivityFilters.sendUpdate = function(page) {
        if (typeof page == 'undefined') {
            page = 1;
        }

        // generate a list of active categories if not all categories are active
        if (!this.isAllSelected()) {
            this.filters.categories = this.list
                                            .find('.friends-activity-filter.active')
                                            .map(function(i, e) {
                                                return $(e).data('filter-name');
                                            })
                                            .get();
        }
        else {
            this.filters.categories = 'all';
        }

        var active_filters = JSON.stringify(this.filters);

        this.setCookie(active_filters);
        var url = window.location.pathname;
        url = url + (page > 1 ? '?page=' + page : '');

        // initialize options for AJAX request
        var options = {
            url: url,
            data: { filters: active_filters }
        };

        // Send the AJAX request to update the page
        $.request(this.list.data('filter-component'), options);
    };

    ActivityFilters.updateState = function() {
        var active_filters = JSON.parse(this.getCookie());

        if (!active_filters || active_filters.categories == 'all') return false;

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
            return true;
        }
        return false;
    };

    ActivityFilters.listenToPageButtons = function() {
        $('.pagination a').click(function() {
            var page = $(this).attr('href').match(/(\?|&)page=(\d+)/);
            page = page ? page[2] : 1;

            ActivityFilters.sendUpdate(page);

            return false;
        });
    };

    ActivityFilters.listen = function() {
        // If we have a saved filter state, update the newly loaded list to reflect it
        if (this.updateState()) this.sendUpdate();

        $('a.friends-activity-filter-all').click(function() {
            if (ActivityFilters.isAllSelected()) {
                // Deselect all
                ActivityFilters.list.find('a.active').removeClass('active').addClass('inactive');
            }
            else {
                // Select all
                ActivityFilters.list.find('a.inactive').removeClass('inactive').addClass('active');
            }
            
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

            ActivityFilters.sendUpdate();
            return false;
        });

        this.listenToPageButtons();
        $('.filtered-activity-list').on('ajaxUpdate', function(e) { ActivityFilters.listenToPageButtons(); });
    };

    ActivityFilters.listen();

})(jQuery);
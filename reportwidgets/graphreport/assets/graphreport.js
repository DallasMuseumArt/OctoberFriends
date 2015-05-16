var friendsReports = {};
friendsReports.graphs = {};

(function($) {

    /**
     * Provides some default settings to use across all graphs
     */
    friendsReports.defaultGraphSettings = {
        color: {
            pattern: ['#95B753', '#CC3300'],
        },
        zoom: {
            enabled: true,
            rescale: true,
        },
    }

    friendsReports.init = function() {
        for (var key in friendsReports.graphs) {
            this.processRequest(friendsReports.graphs[key]);
        }
    }

    friendsReports.initGraph = function(obj, settings) {
        $.extend(settings, friendsReports.defaultGraphSettings);

        obj.renderedGraph = c3.generate(settings);
    }

    friendsReports.processRequest = function(obj) {
        $.ajax({
            url: obj.ajaxPath,
            dataType: 'json',
            data: {
                to: friendsReports.toDate,
                from: friendsReports.fromDate,
            },
            type: 'GET',
            error: function() {
                alert('something went wrong');
            },
            success: function(data) {
                obj.data = data;
                obj.chart();
            }
        });
    }

})(window.jQuery);
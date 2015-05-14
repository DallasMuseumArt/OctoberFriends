var friendsReports = {};
friendsReports.graphs = {};

(function($) {

    friendsReports.init = function() {
        for (var key in friendsReports.graphs) {
            this.processRequest(friendsReports.graphs[key]);
        }
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
var friendsReports = {};
friendsReports.graphs = {};

(function($) {

    $(document).ready(function() {
        friendsReports.init();  
    });

    friendsReports.init = function() {
        for (var key in friendsReports.graphs) {
            console.log(key);
            this.processRequest(friendsReports.graphs[key]);
        }
    }

    friendsReports.processRequest = function(obj) {
        $.ajax({
            url: obj.ajaxPath,
            dataType: 'json',
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
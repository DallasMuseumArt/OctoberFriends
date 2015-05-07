var friendsReports = {};

(function($) {

    $(document).ready(function() {
        for (var key in friendsReports) {
            processRequest(friendsReports[key]);
        }
    });

    function processRequest(obj)
    {
        $.ajax({
            url: obj.ajaxPath,
            dataType: 'json',
            type: 'GET',
            error: function() {
                alert('something went wrong');
            },
            success: function(data) {
                console.log(obj.chart);
                obj.data = data;
                obj.chart();
            }
        });
    }

})(window.jQuery);
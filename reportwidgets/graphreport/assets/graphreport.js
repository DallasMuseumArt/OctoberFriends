var friendsReports = {};

(function($) {

    $(document).ready(function() {
        for (var key in friendsReports) {

            $.ajax({
                url: friendsReports[key].ajaxPath,
                dataType: 'json',
                type: 'GET',
                error: function() {
                    alert('something went wrong');
                },
                success: function(data) {
                    friendsReports[key].data = data;
                    friendsReports[key].chart();
                }
            });

        }
    });

})(window.jQuery);
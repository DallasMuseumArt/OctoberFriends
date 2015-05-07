var friendsReports = {};

(function($) {

    $(document).ready(function() {
        for (var key in friendsReports) {
            console.log(key);

            // TODO: instead of listening for an event, instantiate an ajax call and run chart() after the request is complete
            // populate data in the success() call

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
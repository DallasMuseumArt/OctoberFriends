(function($) {
    
    $(document).ready(function() {

        // Preset dates
        $('.date-range').click(function() {
            $('#datepicker-from').val($(this).data('from'));
            $('#datepicker-to').val($(this).data('to'));
             
            $('.date-range.active').removeClass('active');
            $(this).addClass('active');

            $('.apply-dates').click();
        });

        // Custom dates and picker
        var format = 'yy-mm-dd'

        $('#datepicker-from').datepicker({
            dateFormat: format,
            maxDate: 'now',
        });

        $('#datepicker-to').datepicker({
            dateFormat: format,
            maxDate: 'now',
        });

        $('.apply-dates').on('click', function(e) {
            e.preventDefault();

            $('.graphreport').html('<img class="graphreport-loading" src="/plugins/dma/friends/reportwidgets/graphreport/assets/images/loading.gif"/>');

            var from = $('#datepicker-from').val();
            var to = $('#datepicker-to').val();

            friendsReports.fromDate = from;
            friendsReports.toDate = to;

            friendsReports.init(); 

        });

        // Default to week view
        $('.date-picker .week').click();
    });

})(window.jQuery);